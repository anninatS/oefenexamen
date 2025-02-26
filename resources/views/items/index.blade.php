<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Item Catalog') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <form action="{{ route('items.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4">
                            <div class="flex flex-col md:flex-row gap-4 flex-wrap w-full">
                                <!-- Item Type Filter -->
                                <div class="w-full md:w-auto">
                                    <label for="type" class="block text-sm font-medium text-gray-700">Item Type</label>
                                    <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="all">All Types</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type }}" {{ isset($filters['type']) && $filters['type'] == $type ? 'selected' : '' }}>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Rarity Filter -->
                                <div class="w-full md:w-auto">
                                    <label for="rarity" class="block text-sm font-medium text-gray-700">Rarity</label>
                                    <select id="rarity" name="rarity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="all">All Rarities</option>
                                        @foreach($rarities as $rarity)
                                            <option value="{{ $rarity }}" {{ isset($filters['rarity']) && $filters['rarity'] == $rarity ? 'selected' : '' }}>
                                                {{ ucfirst($rarity) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Minimum Stats Filters -->
                                <div class="w-full md:w-auto">
                                    <label for="min_strength" class="block text-sm font-medium text-gray-700">Min Strength</label>
                                    <input type="number" id="min_strength" name="min_strength" min="0" max="100"
                                           value="{{ $filters['min_strength'] ?? '' }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div class="w-full md:w-auto">
                                    <label for="min_speed" class="block text-sm font-medium text-gray-700">Min Speed</label>
                                    <input type="number" id="min_speed" name="min_speed" min="0" max="100"
                                           value="{{ $filters['min_speed'] ?? '' }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div class="w-full md:w-auto">
                                    <label for="min_durability" class="block text-sm font-medium text-gray-700">Min Durability</label>
                                    <input type="number" id="min_durability" name="min_durability" min="0" max="100"
                                           value="{{ $filters['min_durability'] ?? '' }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="flex items-end space-x-2">
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Filter
                                </button>
                                <a href="{{ route('items.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    @if($items->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach($items as $item)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                                    <div class="p-4">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $item->name }}</h3>
                                        <div class="flex items-center mb-2">
                                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                                @if($item->rarity == 'common') bg-gray-200 text-gray-800
                                                @elseif($item->rarity == 'uncommon') bg-green-200 text-green-800
                                                @elseif($item->rarity == 'rare') bg-blue-200 text-blue-800
                                                @elseif($item->rarity == 'epic') bg-purple-200 text-purple-800
                                                @elseif($item->rarity == 'legendary') bg-yellow-200 text-yellow-800
                                                @elseif($item->rarity == 'mythic') bg-red-200 text-red-800
                                                @endif">
                                                {{ ucfirst($item->rarity) }}
                                            </span>
                                            <span class="ml-2 text-sm text-gray-600">{{ ucfirst($item->type) }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $item->description }}</p>

                                        <div class="grid grid-cols-3 gap-2 mb-3">
                                            <div class="text-center">
                                                <span class="block text-xs text-gray-500">Strength</span>
                                                <span class="font-semibold">{{ $item->strength }}</span>
                                            </div>
                                            <div class="text-center">
                                                <span class="block text-xs text-gray-500">Speed</span>
                                                <span class="font-semibold">{{ $item->speed }}</span>
                                            </div>
                                            <div class="text-center">
                                                <span class="block text-xs text-gray-500">Durability</span>
                                                <span class="font-semibold">{{ $item->durability }}</span>
                                            </div>
                                        </div>

                                        <a href="{{ route('items.show', $item) }}" class="block w-full text-center py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $items->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <h3 class="text-lg font-medium text-gray-900">No items found</h3>
                            <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or check back later for new items.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
