<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Item;
use App\Utility\MaxDurabilityUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Display a listing of the user's inventory.
     */
    public function index(Request $request): View
    {
        $query = Inventory::with('item')
            ->where('user_id', $request->user()->id);

        // Filter by item type if requested
        if ($request->has('type') && $request->type !== 'all') {
            $query->whereHas('item', function ($query) use ($request) {
                $query->where('type', $request->type);
            });
        }

        // Filter by rarity if requested
        if ($request->has('rarity') && $request->rarity !== 'all') {
            $query->whereHas('item', function ($query) use ($request) {
                $query->where('rarity', $request->rarity);
            });
        }

        // Filter by minimum strength if requested
        if ($request->has('min_strength') && is_numeric($request->min_strength)) {
            $query->whereHas('item', function ($query) use ($request) {
                $query->where('strength', '>=', $request->min_strength);
            });
        }

        // Filter by minimum speed if requested
        if ($request->has('min_speed') && is_numeric($request->min_speed)) {
            $query->whereHas('item', function ($query) use ($request) {
                $query->where('speed', '>=', $request->min_speed);
            });
        }

        // Filter by minimum durability if requested
        if ($request->has('min_durability') && is_numeric($request->min_durability)) {
            $query->whereHas('item', function ($query) use ($request) {
                $query->where('durability', '>=', $request->min_durability);
            });
        }

        // Get distinct item types and rarities for filter dropdowns
        $types = $request->user()->inventories()
            ->join('items', 'inventories.item_id', '=', 'items.id')
            ->distinct()
            ->pluck('items.type');

        $rarities = $request->user()->inventories()
            ->join('items', 'inventories.item_id', '=', 'items.id')
            ->distinct()
            ->pluck('items.rarity');

        // Get the inventory items with pagination
        $inventoryItems = $query->paginate(12)->withQueryString();

        return view('inventory.index', [
            'inventoryItems' => $inventoryItems,
            'types' => $types,
            'rarities' => $rarities,
            'filters' => $request->only(['type', 'rarity', 'min_strength', 'min_speed', 'min_durability']),
        ]);
    }

    /**
     * Display the specified inventory item.
     */
    public function show(Request $request, Inventory $inventory): View
    {
        // Check if the inventory item belongs to the authenticated user
        if ($inventory->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $durability = MaxDurabilityUtility::getMaxDurability();
        $maxDurability = $durability['maxDurability'];
        $durabilityPercentage = $durability['durabilityPercentage'];

        return view('inventory.show', [
            'inventory' => $inventory->load('item'),
            'maxDurability' => $maxDurability,
            'durabilityPercentage' => $durabilityPercentage,
        ]);
    }
}
