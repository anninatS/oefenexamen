<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Notification;
use App\Models\TradeItem;
use App\Models\TradeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $senderItems = $user->inventories()
            ->with('item')
            ->get();

        // Get all other users that could be trade partners
        $potentialReceivers = User::where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        return view('trades.create', [
            'senderItems' => $senderItems,
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
            'sender_items' => ['required_without:receiver_items', 'array'],
            'sender_items.*' => ['exists:inventories,id'],
            'receiver_items' => ['required_without:sender_items', 'array'],
            'receiver_items.*' => ['exists:inventories,id'],
        ]);

        $user = $request->user();
        $receiverId = $request->receiver_id;

        // Prevent trading with yourself
        if ($user->id === (int)$receiverId) {
            return back()->withErrors(['receiver_id' => 'You cannot trade with yourself.'])->withInput();
        }

        // Ensure at least one side has items
        if (empty($request->sender_items) && empty($request->receiver_items)) {
            return back()->withErrors(['items' => 'At least one side must offer items for the trade.'])->withInput();
        }

        // Verify the selected sender items belong to the user
        if (!empty($request->sender_items)) {
            $itemCount = Inventory::where('user_id', $user->id)
                ->whereIn('id', $request->sender_items)
                ->count();

            if ($itemCount !== count($request->sender_items)) {
                return back()->withErrors(['sender_items' => 'Some of the selected items do not belong to you.'])->withInput();
            }
        }

        // Verify the selected receiver items belong to the receiver
        if (!empty($request->receiver_items)) {
            $itemCount = Inventory::where('user_id', $receiverId)
                ->whereIn('id', $request->receiver_items)
                ->count();

            if ($itemCount !== count($request->receiver_items)) {
                return back()->withErrors(['receiver_items' => 'Some of the selected items do not belong to the receiver.'])->withInput();
            }
        }

        // Check if any of the selected items are already in pending trade requests
        $allItems = array_merge($request->sender_items ?? [], $request->receiver_items ?? []);
        $pendingTradeItems = TradeItem::whereIn('inventory_id', $allItems)
            ->whereHas('tradeRequest', function($query) {
                $query->whereIn('status', [
                    TradeRequest::STATUS_PENDING,
                    TradeRequest::STATUS_MODIFIED
                ]);
            })
            ->count();

        if ($pendingTradeItems > 0) {
            return back()->withErrors(['items' => 'One or more selected items are already part of pending trade requests. Please finalize or cancel those trades first.'])->withInput();
        }

        try {
            return DB::transaction(function() use ($user, $receiverId, $request) {
                // Create the trade request
                $tradeRequest = TradeRequest::create([
                    'sender_id' => $user->id,
                    'receiver_id' => $receiverId,
                    'status' => TradeRequest::STATUS_PENDING,
                ]);

                // Add the sender's items to the trade request
                if (!empty($request->sender_items)) {
                    foreach ($request->sender_items as $inventoryItemId) {
                        TradeItem::create([
                            'trade_request_id' => $tradeRequest->id,
                            'inventory_id' => $inventoryItemId,
                            'direction' => TradeItem::DIRECTION_OFFER,
                        ]);
                    }
                }

                // Add the receiver's items to the trade request
                if (!empty($request->receiver_items)) {
                    foreach ($request->receiver_items as $inventoryItemId) {
                        TradeItem::create([
                            'trade_request_id' => $tradeRequest->id,
                            'inventory_id' => $inventoryItemId,
                            'direction' => TradeItem::DIRECTION_REQUEST,
                        ]);
                    }
                }

                // Create a notification for the receiver
                $receiver = User::find($receiverId);
                Notification::create([
                    'user_id' => $receiver->id,
                    'type' => Notification::TYPE_TRADE_REQUEST,
                    'message' => "{$user->name} has sent you a trade request",
                    'read' => false,
                ]);

                return redirect()->route('trades.show', $tradeRequest)
                    ->with('status', 'Trade request sent successfully.');
            });
        } catch (\Exception $e) {
            Log::error('Failed to create trade request: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create trade request. Please try again.'])->withInput();
        }
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

        // Determine if the current user is the sender
        $isSender = $trade->sender_id === $user->id;

        // Group trade items by the current user's perspective
        if ($isSender) {
            // User is the sender
            $userOfferingItems = $trade->tradeItems->filter(function ($item) {
                return $item->direction === TradeItem::DIRECTION_OFFER;
            });

            $userReceivingItems = $trade->tradeItems->filter(function ($item) {
                return $item->direction === TradeItem::DIRECTION_REQUEST;
            });
        } else {
            // User is the receiver
            $userOfferingItems = $trade->tradeItems->filter(function ($item) {
                return $item->direction === TradeItem::DIRECTION_REQUEST;
            });

            $userReceivingItems = $trade->tradeItems->filter(function ($item) {
                return $item->direction === TradeItem::DIRECTION_OFFER;
            });
        }

        return view('trades.show', [
            'tradeRequest' => $trade,
            'isSender' => $isSender,
            'senderItems' => $userOfferingItems,  // What the current user is offering
            'receiverItems' => $userReceivingItems, // What the current user will receive
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
        $action = $request->action;

        // Check if the user has appropriate permissions for this action
        if ($action === 'cancel') {
            // Only the sender can cancel a pending trade
            // Or the person who didn't modify a modified trade
            if ($trade->status === TradeRequest::STATUS_PENDING) {
                if ($trade->sender_id !== $user->id) {
                    abort(403, 'You are not authorized to cancel this trade request.');
                }
            } elseif ($trade->status === TradeRequest::STATUS_MODIFIED) {
                if ($trade->modified_by_id === $user->id) {
                    abort(403, 'You cannot cancel a trade you just modified. Either wait for the other party to respond or remove all items.');
                }
            } else {
                abort(403, 'This trade request can no longer be cancelled.');
            }
        } else {
            // For accept/reject, check if this user can approve/reject the trade
            if (!$trade->canBeApprovedBy($user)) {
                // For modified trades, explain clearly
                if ($trade->isModified()) {
                    if ($trade->modified_by_id === $user->id) {
                        abort(403, 'You cannot accept/reject a trade you just modified. Wait for the other party to respond.');
                    } elseif (($user->id === $trade->sender_id && $trade->sender_approved) ||
                        ($user->id === $trade->receiver_id && $trade->receiver_approved)) {
                        abort(403, 'You have already approved this trade. Waiting for the other party.');
                    }
                } else {
                    abort(403, 'You are not authorized to accept or reject this trade request.');
                }
            }
        }

        // Check if the trade request is active
        if (!$trade->isActive()) {
            return back()->withErrors(['trade' => 'This trade request has already been processed.']);
        }

        try {
            if ($action === 'accept') {
                return $this->acceptTrade($trade, $user);
            } elseif ($action === 'reject') {
                return $this->rejectTrade($trade, $user);
            } else { // cancel
                return $this->cancelTrade($trade, $user);
            }
        } catch (\Exception $e) {
            Log::error('Failed to process trade action: ' . $e->getMessage());
            return back()->withErrors(['trade' => 'An error occurred while processing the trade. Please try again.']);
        }
    }

    /**
     * Accept a trade request.
     */
    protected function acceptTrade(TradeRequest $trade, User $user): RedirectResponse
    {
        return DB::transaction(function() use ($trade, $user) {
            // Handle differently based on the current status
            if ($trade->isModified()) {
                // Record approval
                if ($user->id === $trade->sender_id) {
                    $trade->sender_approved = true;
                } else {
                    $trade->receiver_approved = true;
                }

                // If both have approved, proceed with acceptance
                if (($trade->sender_approved && $trade->receiver_id === $user->id) ||
                    ($trade->receiver_approved && $trade->sender_id === $user->id)) {
                    // Both parties have now approved - proceed to actual acceptance
                    $trade->status = TradeRequest::STATUS_PENDING; // Reset to pending for the next block to handle
                    $trade->save();
                } else {
                    // Just one party has approved - save and wait for the other
                    $trade->save();

                    // Create a notification for the other party
                    $otherPartyId = ($user->id === $trade->sender_id) ? $trade->receiver_id : $trade->sender_id;
                    Notification::create([
                        'user_id' => $otherPartyId,
                        'type' => Notification::TYPE_TRADE_UPDATED,
                        'message' => "{$user->name} has approved the modified trade. Your approval is needed to complete the trade.",
                        'read' => false,
                    ]);

                    return redirect()->route('trades.index')
                        ->with('status', 'You have approved the modified trade. Waiting for the other party to approve.');
                }
            }

            // For pending trades or fully approved modified trades
            if ($trade->isPending()) {
                // First verify that all items still exist and are owned by the respective users
                $tradeItems = $trade->tradeItems()->with('inventory.item')->get();
                $senderId = $trade->sender_id;
                $receiverId = $trade->receiver_id;

                // Lock the inventories to prevent concurrent modifications
                $inventoryIds = $tradeItems->pluck('inventory_id')->toArray();
                Inventory::whereIn('id', $inventoryIds)->lockForUpdate()->get();

                foreach ($tradeItems as $tradeItem) {
                    // Check if inventory or item has been deleted
                    if (!$tradeItem->inventory || !$tradeItem->inventory->item) {
                        return back()->withErrors(['trade' => 'This trade cannot be completed because one or more items no longer exist.']);
                    }

                    // Check if the correct user still owns the item
                    $expectedOwnerId = $tradeItem->isOffer() ? $senderId : $receiverId;
                    if ($tradeItem->inventory->user_id !== $expectedOwnerId) {
                        return back()->withErrors(['trade' => 'This trade cannot be completed because one or more items are no longer owned by the expected user.']);
                    }

                    // Check if the item is involved in another pending trade
                    $otherPendingTrades = TradeItem::where('inventory_id', $tradeItem->inventory_id)
                        ->where('trade_request_id', '!=', $trade->id)
                        ->whereHas('tradeRequest', function($query) {
                            $query->whereIn('status', [
                                TradeRequest::STATUS_PENDING,
                                TradeRequest::STATUS_MODIFIED
                            ]);
                        })
                        ->exists();

                    if ($otherPendingTrades) {
                        return back()->withErrors(['trade' => 'One or more items are now part of other pending trades. This trade cannot be completed.']);
                    }
                }

                // Accept the trade request
                $trade->status = TradeRequest::STATUS_ACCEPTED;
                $trade->save();

                // Process the trade (update item ownership)
                foreach ($tradeItems as $tradeItem) {
                    $inventory = $tradeItem->inventory;
                    $newOwnerId = $tradeItem->isOffer() ? $receiverId : $senderId;

                    // Update the owner of each item
                    $inventory->user_id = $newOwnerId;
                    $inventory->acquired_at = time();
                    $inventory->save();

                    // Create notification for item received
                    $recipientId = $tradeItem->isOffer() ? $receiverId : $senderId;
                    $sendingUsername = $tradeItem->isOffer() ? $trade->sender->name : $trade->receiver->name;

                    Notification::create([
                        'user_id' => $recipientId,
                        'type' => Notification::TYPE_ITEM_RECEIVED,
                        'message' => "You received {$tradeItem->inventory->item->name} in a trade with {$sendingUsername}",
                        'read' => false,
                    ]);
                }

                // Create a notification for the other party
                $otherPartyId = ($user->id === $trade->sender_id) ? $trade->receiver_id : $trade->sender_id;
                Notification::create([
                    'user_id' => $otherPartyId,
                    'type' => Notification::TYPE_TRADE_ACCEPTED,
                    'message' => "{$user->name} has accepted the trade request",
                    'read' => false,
                ]);

                return redirect()->route('trades.index')
                    ->with('status', 'Trade request accepted successfully.');
            }

            // Should not reach here, but just in case
            return redirect()->route('trades.index')
                ->with('status', 'Trade request processed.');
        });
    }

    /**
     * Reject a trade request.
     */
    protected function rejectTrade(TradeRequest $trade, User $user): RedirectResponse
    {
        // If this is a modified trade, check approvals first
        if ($trade->isModified()) {
            // Reset approvals
            $trade->sender_approved = null;
            $trade->receiver_approved = null;
        }

        // Reject the trade request
        $trade->status = TradeRequest::STATUS_REJECTED;
        $trade->save();

        // Create a notification for the other party
        $otherPartyId = ($user->id === $trade->sender_id) ? $trade->receiver_id : $trade->sender_id;
        Notification::create([
            'user_id' => $otherPartyId,
            'type' => Notification::TYPE_TRADE_REJECTED,
            'message' => "{$user->name} has rejected the trade request",
            'read' => false,
        ]);

        return redirect()->route('trades.index')
            ->with('status', 'Trade request rejected successfully.');
    }

    /**
     * Cancel a trade request.
     */
    protected function cancelTrade(TradeRequest $trade, User $user): RedirectResponse
    {
        // Cancel the trade request - using distinct STATUS_CANCELLED now
        $trade->status = TradeRequest::STATUS_CANCELLED;
        $trade->save();

        // Create a notification for the other party
        $otherPartyId = ($user->id === $trade->sender_id) ? $trade->receiver_id : $trade->sender_id;
        Notification::create([
            'user_id' => $otherPartyId,
            'type' => Notification::TYPE_TRADE_REJECTED, // Reusing the rejection notification type
            'message' => "{$user->name} has cancelled the trade request",
            'read' => false,
        ]);

        return redirect()->route('trades.index')
            ->with('status', 'Trade request cancelled successfully.');
    }

    /**
     * Get the inventory items for a specific user.
     */
    public function getUserItems(Request $request): RedirectResponse|View
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'trade_id' => ['nullable', 'exists:trade_requests,id'],
        ]);

        $targetUser = User::findOrFail($request->user_id);
        $currentUser = $request->user();

        // Check if the user is authorized to view this trade
        if ($request->has('trade_id')) {
            $trade = TradeRequest::findOrFail($request->trade_id);
            if ($trade->sender_id !== $currentUser->id && $trade->receiver_id !== $currentUser->id) {
                abort(403, 'You are not authorized to view this trade request.');
            }
        }

        $userItems = $targetUser->inventories()
            ->with('item')
            ->get();

        return view('trades.user_items', [
            'user' => $targetUser,
            'inventoryItems' => $userItems,
            'trade_id' => $request->trade_id,
        ]);
    }

    /**
     * Show the form for adding items to an existing trade request.
     */
    public function edit(Request $request, TradeRequest $trade): View
    {
        $user = $request->user();

        // Check if the user is involved in this trade
        if ($trade->sender_id !== $user->id && $trade->receiver_id !== $user->id) {
            abort(403, 'You are not authorized to edit this trade request.');
        }

        // Check if the trade is still pending
        if (!$trade->isPending()) {
            return redirect()->route('trades.show', $trade)
                ->withErrors(['trade' => 'This trade request cannot be edited because it has already been processed.']);
        }

        $isSender = $trade->sender_id === $user->id;

        // Get the current trade items
        $trade->load(['tradeItems.inventory.item']);

        // Get the current user's inventory
        $userItems = $user->inventories()
            ->with('item')
            ->whereNotIn('id', $trade->tradeItems->pluck('inventory_id')->toArray())
            ->get();

        return view('trades.edit', [
            'tradeRequest' => $trade,
            'isSender' => $isSender,
            'userItems' => $userItems,
        ]);
    }

    /**
     * Remove an item from an existing trade request.
     */
    public function removeItem(Request $request, TradeRequest $trade, TradeItem $item): RedirectResponse
    {
        $user = $request->user();

        // Check if the user is involved in this trade
        if ($trade->sender_id !== $user->id && $trade->receiver_id !== $user->id) {
            abort(403, 'You are not authorized to update this trade request.');
        }

        // Check if the trade is still active
        if (!$trade->isActive()) {
            return redirect()->route('trades.show', $trade)
                ->withErrors(['trade' => 'This trade request cannot be updated because it has already been finalized.']);
        }

        // Check if the item belongs to this trade
        if ($item->trade_request_id !== $trade->id) {
            abort(404, 'The specified item does not belong to this trade request.');
        }

        // Check if the user can remove this item
        $isSender = $trade->sender_id === $user->id;
        if (($isSender && $item->direction !== TradeItem::DIRECTION_OFFER) ||
            (!$isSender && $item->direction !== TradeItem::DIRECTION_REQUEST)) {
            abort(403, 'You can only remove your own items from the trade.');
        }

        try {
            return DB::transaction(function() use ($trade, $item, $user, $isSender) {
                // Remove the item
                $item->delete();

                // Check if there are any items left in the trade
                $remainingItems = $trade->tradeItems()->count();
                if ($remainingItems === 0) {
                    // If no items left, cancel the trade
                    $trade->status = TradeRequest::STATUS_CANCELLED;
                    $trade->save();

                    // Notify the other party
                    $targetUserId = $isSender ? $trade->receiver_id : $trade->sender_id;
                    Notification::create([
                        'user_id' => $targetUserId,
                        'type' => Notification::TYPE_TRADE_REJECTED,
                        'message' => "{$user->name} has cancelled the trade request by removing all items",
                        'read' => false,
                    ]);

                    return redirect()->route('trades.index')
                        ->with('status', 'Trade request cancelled because all items were removed.');
                }

                // Mark trade as modified and record who modified it
                $trade->resetApprovals($user->id);

                // Notify the other party about the update
                $targetUserId = $isSender ? $trade->receiver_id : $trade->sender_id;
                Notification::create([
                    'user_id' => $targetUserId,
                    'type' => Notification::TYPE_TRADE_UPDATED,
                    'message' => "{$user->name} has removed an item from the trade request. Please review and approve the changes.",
                    'read' => false,
                ]);

                return redirect()->route('trades.show', $trade)
                    ->with('status', 'Item removed from trade successfully. The other party must approve the changes.');
            });
        } catch (\Exception $e) {
            Log::error('Failed to remove trade item: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to remove item from trade. Please try again.']);
        }
    }

    /**
     * Update the items in an existing trade request.
     */
    public function addItems(Request $request, TradeRequest $trade): RedirectResponse
    {
        $user = $request->user();

        // Check if the user is involved in this trade
        if ($trade->sender_id !== $user->id && $trade->receiver_id !== $user->id) {
            abort(403, 'You are not authorized to update this trade request.');
        }

        // Check if the trade is still pending or modified
        if (!$trade->isActive()) {
            return redirect()->route('trades.show', $trade)
                ->withErrors(['trade' => 'This trade request cannot be updated because it has already been processed.']);
        }

        // If trade is modified, check if this user can modify it
        if ($trade->isModified() && $trade->modified_by_id === $user->id) {
            if (($user->id === $trade->sender_id && $trade->sender_approved) ||
                ($user->id === $trade->receiver_id && $trade->receiver_approved)) {
                abort(403, 'You cannot modify a trade you just approved. Wait for the other party to respond.');
            }
        }

        $request->validate([
            'inventory_items' => ['required', 'array', 'min:1'],
            'inventory_items.*' => ['exists:inventories,id'],
        ]);

        $isSender = $trade->sender_id === $user->id;
        $direction = $isSender ? TradeItem::DIRECTION_OFFER : TradeItem::DIRECTION_REQUEST;

        // Verify the selected items belong to the user
        $itemCount = Inventory::where('user_id', $user->id)
            ->whereIn('id', $request->inventory_items)
            ->count();

        if ($itemCount !== count($request->inventory_items)) {
            return back()->withErrors(['inventory_items' => 'Some of the selected items do not belong to you.'])->withInput();
        }

        // Check if any of the selected items are already in pending trade requests
        $pendingTradeItems = TradeItem::whereIn('inventory_id', $request->inventory_items)
            ->whereHas('tradeRequest', function($query) use ($trade) {
                $query->whereIn('status', [
                    TradeRequest::STATUS_PENDING,
                    TradeRequest::STATUS_MODIFIED
                ])
                    ->where('id', '!=', $trade->id);
            })
            ->count();

        if ($pendingTradeItems > 0) {
            return back()->withErrors(['inventory_items' => 'One or more selected items are already part of pending trade requests. Please finalize or cancel those trades first.'])->withInput();
        }

        try {
            return DB::transaction(function() use ($trade, $user, $request, $direction, $isSender) {
                // Add the new items to the trade request
                foreach ($request->inventory_items as $inventoryItemId) {
                    TradeItem::create([
                        'trade_request_id' => $trade->id,
                        'inventory_id' => $inventoryItemId,
                        'direction' => $direction,
                    ]);
                }

                // Mark the trade as modified and reset approvals
                $trade->resetApprovals($user->id);

                // Create a notification for the other party
                $targetUserId = $isSender ? $trade->receiver_id : $trade->sender_id;
                Notification::create([
                    'user_id' => $targetUserId,
                    'type' => Notification::TYPE_TRADE_UPDATED,
                    'message' => "{$user->name} has updated the trade request with additional items. Please review and approve the changes.",
                    'read' => false,
                ]);

                return redirect()->route('trades.show', $trade)
                    ->with('status', 'Trade request updated successfully. The other party must approve the changes.');
            });
        } catch (\Exception $e) {
            Log::error('Failed to add items to trade: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to add items to trade. Please try again.']);
        }
    }
}
