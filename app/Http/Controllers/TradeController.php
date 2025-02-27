<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Notification;
use App\Models\TradeItem;
use App\Models\TradeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TradeController extends Controller
{
    /**
     * Display a listing of the trade requests (sent and received).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $sentTradeRequests = $user->sentTradeRequests()
            ->with(['receiver', 'tradeItems.inventory.item'])
            ->latest()
            ->paginate(10, ['*'], 'sent_page');

        $receivedTradeRequests = $user->receivedTradeRequests()
            ->with(['sender', 'tradeItems.inventory.item'])
            ->latest()
            ->paginate(10, ['*'], 'received_page');

        return view('trades.index', [
            'sentTradeRequests' => $sentTradeRequests,
            'receivedTradeRequests' => $receivedTradeRequests,
        ]);
    }

    /**
     * Show the form for creating a new trade request.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        // Get the user's inventory items
        $inventoryItems = $user->inventories()
            ->with('item')
            ->get();

        // Get all other users that could be trade partners
        $potentialReceivers = User::where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        return view('trades.create', [
            'inventoryItems' => $inventoryItems,
            'potentialReceivers' => $potentialReceivers,
        ]);
    }

    /**
     * Store a newly created trade request in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'inventory_items' => ['required', 'array', 'min:1'],
            'inventory_items.*' => ['exists:inventories,id'],
        ]);

        $user = $request->user();

        // Verify the selected items belong to the user
        $itemCount = Inventory::where('user_id', $user->id)
            ->whereIn('id', $request->inventory_items)
            ->count();

        if ($itemCount !== count($request->inventory_items)) {
            return back()->withErrors(['inventory_items' => 'Some of the selected items do not belong to you.'])->withInput();
        }

        // Check if any of the selected items are already in pending trade requests
        $pendingTradeItems = TradeItem::whereIn('inventory_id', $request->inventory_items)
            ->whereHas('tradeRequest', function($query) {
                $query->where('status', TradeRequest::STATUS_PENDING);
            })
            ->count();

        if ($pendingTradeItems > 0) {
            return back()->withErrors(['inventory_items' => 'One or more selected items are already part of pending trade requests. Please finalize or cancel those trades first.'])->withInput();
        }

        // Create the trade request
        $tradeRequest = TradeRequest::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'status' => TradeRequest::STATUS_PENDING,
        ]);

        // Add the items to the trade request
        foreach ($request->inventory_items as $inventoryItemId) {
            TradeItem::create([
                'trade_request_id' => $tradeRequest->id,
                'inventory_id' => $inventoryItemId,
            ]);
        }

        // Create a notification for the receiver
        $receiver = User::find($request->receiver_id);
        Notification::create([
            'user_id' => $receiver->id,
            'type' => Notification::TYPE_TRADE_REQUEST,
            'message' => "{$user->name} has sent you a trade request",
            'read' => false,
        ]);

        return redirect()->route('trades.show', $tradeRequest)
            ->with('status', 'Trade request sent successfully.');
    }

    /**
     * Display the specified trade request.
     */
    public function show(Request $request, TradeRequest $trade): View
    {
        $user = $request->user();

        // Check if the user is involved in this trade
        if ($trade->sender_id !== $user->id && $trade->receiver_id !== $user->id) {
            abort(403, 'You are not authorized to view this trade request.');
        }

        $trade->load(['sender', 'receiver', 'tradeItems.inventory.item']);

        return view('trades.show', [
            'tradeRequest' => $trade,
            'isSender' => $trade->sender_id === $user->id,
        ]);
    }

    /**
     * Update the specified trade request (accept, reject, or cancel).
     */
    public function update(Request $request, TradeRequest $trade): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:accept,reject,cancel'],
        ]);

        $user = $request->user();

        // Check if the user has appropriate permissions for this action
        if ($request->action === 'cancel') {
            // Only the sender can cancel
            if ($trade->sender_id !== $user->id) {
                abort(403, 'You are not authorized to cancel this trade request.');
            }
        } else {
            // Only the receiver can accept or reject
            if ($trade->receiver_id !== $user->id) {
                abort(403, 'You are not authorized to accept or reject this trade request.');
            }
        }

        // Check if the trade request is pending
        if (!$trade->isPending()) {
            return back()->withErrors(['trade' => 'This trade request has already been processed.']);
        }

        if ($request->action === 'accept') {
            // First verify that all items still exist and are owned by the sender
            $tradeItems = $trade->tradeItems()->with('inventory.item')->get();

            foreach ($tradeItems as $tradeItem) {
                // Check if inventory or item has been deleted
                if (!$tradeItem->inventory || !$tradeItem->inventory->item) {
                    return back()->withErrors(['trade' => 'This trade cannot be completed because one or more items no longer exist.']);
                }

                // Check if sender still owns the item
                if ($tradeItem->inventory->user_id !== $trade->sender_id) {
                    return back()->withErrors(['trade' => 'This trade cannot be completed because the sender no longer owns one or more items.']);
                }
            }

            // Accept the trade request
            $trade->status = TradeRequest::STATUS_ACCEPTED;
            $trade->save();

            // Process the trade (update item ownership)
            foreach ($tradeItems as $tradeItem) {
                $inventory = $tradeItem->inventory;

                // Update the owner of each item
                $inventory->user_id = $user->id;
                $inventory->acquired_at = time();
                $inventory->save();
            }

            // Create a notification for the sender
            Notification::create([
                'user_id' => $trade->sender_id,
                'type' => Notification::TYPE_TRADE_ACCEPTED,
                'message' => "{$user->name} has accepted your trade request",
                'read' => false,
            ]);

            // Create notifications for received items
            foreach ($tradeItems as $tradeItem) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => Notification::TYPE_ITEM_RECEIVED,
                    'message' => "You received {$tradeItem->inventory->item->name} in a trade with {$trade->sender->name}",
                    'read' => false,
                ]);
            }

            return redirect()->route('trades.index')
                ->with('status', 'Trade request accepted successfully.');
        } elseif ($request->action === 'reject') {
            // Reject the trade request
            $trade->status = TradeRequest::STATUS_REJECTED;
            $trade->save();

            // Create a notification for the sender
            Notification::create([
                'user_id' => $trade->sender_id,
                'type' => Notification::TYPE_TRADE_REJECTED,
                'message' => "{$user->name} has rejected your trade request",
                'read' => false,
            ]);

            return redirect()->route('trades.index')
                ->with('status', 'Trade request rejected successfully.');
        } else { // cancel
            // Cancel the trade request
            $trade->status = TradeRequest::STATUS_REJECTED; // We'll use rejected status for cancellations too
            $trade->save();

            // Create a notification for the receiver
            Notification::create([
                'user_id' => $trade->receiver_id,
                'type' => Notification::TYPE_TRADE_REJECTED,
                'message' => "{$user->name} has cancelled their trade request",
                'read' => false,
            ]);

            return redirect()->route('trades.index')
                ->with('status', 'Trade request cancelled successfully.');
        }
    }
}
