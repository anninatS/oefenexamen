<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ItemController extends Controller
{
    /**
     * Display the item catalog.
     */
    public function index(Request $request): View
    {
        $query = Item::query();

        // Filter by item type if requested
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by rarity if requested
        if ($request->has('rarity') && $request->rarity !== 'all') {
            $query->where('rarity', $request->rarity);
        }

        // Filter by minimum strength if requested
        if ($request->has('min_strength') && is_numeric($request->min_strength)) {
            $query->where('strength', '>=', $request->min_strength);
        }

        // Filter by minimum speed if requested
        if ($request->has('min_speed') && is_numeric($request->min_speed)) {
            $query->where('speed', '>=', $request->min_speed);
        }

        // Filter by minimum durability if requested
        if ($request->has('min_durability') && is_numeric($request->min_durability)) {
            $query->where('durability', '>=', $request->min_durability);
        }

        // Get distinct item types and rarities for filter dropdowns
        $types = Item::distinct()->pluck('type');
        $rarities = Item::distinct()->pluck('rarity');

        // Get the items with pagination
        $items = $query->paginate(12)->withQueryString();

        return view('items.index', [
            'items' => $items,
            'types' => $types,
            'rarities' => $rarities,
            'filters' => $request->only(['type', 'rarity', 'min_strength', 'min_speed', 'min_durability']),
        ]);
    }

    /**
     * Display the specified item.
     */
    public function show(Item $item): View
    {
        $cacheKey = 'global_max_durability';

        if (!Cache::has($cacheKey)) {
            $maxDurability = Item::select('durability')
                ->distinct()
                ->max('durability');
            $maxDurability = max($maxDurability, 100);
            Cache::put($cacheKey, $maxDurability, now()->addMinutes(15));
        }

        $maxDurability = Cache::get($cacheKey);
        $durabilityPercentage = ($item->durability / $maxDurability) * 100;

        return view('items.show', [
            'item' => $item,
            'durabilityPercentage' => $durabilityPercentage,
            'maxDurability' => $maxDurability,
        ]);
    }
}
