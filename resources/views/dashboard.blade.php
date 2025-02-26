<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6">{{ __("Welcome, ") }} {{ Auth::user()->name }}!</h2>

                    <!-- Inventory Overview -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4">Your Inventory Overview</h3>

                        @php
                            $inventoryCount = Auth::user()->inventories()->count();
                            $rarityStats = Auth::user()->inventories()
                                ->join('items', 'inventories.item_id', '=', 'items.id')
                                ->selectRaw('items.rarity, count(*) as count')
                                ->groupBy('items.rarity')
                                ->pluck('count', 'rarity')
                                ->toArray();

                            $typeStats = Auth::user()->inventories()
                                ->join('items', 'inventories.item_id', '=', 'items.id')
                                ->selectRaw('items.type, count(*) as count')
                                ->groupBy('items.type')
                                ->pluck('count', 'type')
                                ->toArray();

                            // Get recent acquisitions
                            $recentItems = Auth::user()->inventories()
                                ->with('item')
                                ->orderBy('acquired_at', 'desc')
                                ->take(3)
                                ->get();
                        @endphp

                        @if($inventoryCount > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                                <div class="bg-indigo-100 rounded-lg p-4">
                                    <h4 class="text-lg font-medium text-indigo-800 mb-2">Total Items</h4>
                                    <p class="text-3xl font-bold text-indigo-600">{{ $inventoryCount }}</p>
                                </div>

                                @if(count($rarityStats) > 0)
                                    <div class="bg-purple-100 rounded-lg p-4">
                                        <h4 class="text-lg font-medium text-purple-800 mb-2">Rarest Item</h4>
                                        @php
                                            $rarities = ['mythic', 'legendary', 'epic', 'rare', 'uncommon', 'common'];
                                            $highestRarity = null;
                                            foreach($rarities as $rarity) {
                                                if(isset($rarityStats[$rarity]) && $rarityStats[$rarity] > 0) {
                                                    $highestRarity = $rarity;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        @if($highestRarity)
                                            <div class="flex items-center">
                                                <x-rarity-badge :rarity="$highestRarity" />
                                                <span class="ml-2 text-lg">{{ $rarityStats[$highestRarity] ?? 0 }} items</span>
                                            </div>
                                        @else
                                            <p class="text-gray-600">No items found</p>
                                        @endif
                                    </div>
                                @endif

                                @if(count($typeStats) > 0)
                                    <div class="bg-green-100 rounded-lg p-4">
                                        <h4 class="text-lg font-medium text-green-800 mb-2">Most Common Type</h4>
                                        @php
                                            $mostCommonType = array_search(max($typeStats), $typeStats);
                                        @endphp
                                        <p class="text-xl font-bold text-green-600">
                                            {{ ucfirst($mostCommonType) }}
                                            <span class="text-base font-normal ml-1">({{ $typeStats[$mostCommonType] }} items)</span>
                                        </p>
                                    </div>
                                @endif

                                <div class="bg-amber-100 rounded-lg p-4">
                                    <h4 class="text-lg font-medium text-amber-800 mb-2">Recent Activity</h4>
                                    <p class="text-amber-600">
                                        @if($recentItems->count() > 0)
                                            {{ $recentItems->count() }} items acquired recently
                                        @else
                                            No recent acquisitions
                                        @endif
                                    </p>
                                </div>
                            </div>

                            @if($recentItems->count() > 0)
                                <div class="mb-4">
                                    <h4 class="text-lg font-medium mb-3">Recently Acquired Items</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        @foreach($recentItems as $inventoryItem)
                                            <x-inventory-item-card :inventoryItem="$inventoryItem" />
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4">
                                <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    View Full Inventory
                                </a>
                            </div>
                        @else
                            <div class="bg-white rounded-lg border border-gray-200 p-6 text-center">
                                <p class="text-lg mb-4">You don't have any items in your inventory yet.</p>
                                <a href="{{ route('items.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Browse the Item Catalog
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
