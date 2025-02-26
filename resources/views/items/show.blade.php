<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $item->name }}
            </h2>
            <a href="{{ route('items.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                Back to Catalog
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="md:flex md:gap-8">
                        <!-- Item Image/Icon Placeholder -->
                        <div class="md:w-1/3 mb-6 md:mb-0">
                            <div class="aspect-square rounded-lg flex items-center justify-center {{-- $iconBg --}}">
                                @php
                                    $iconBg = match($item->rarity) {
                                        'common' => 'bg-gray-100',
                                        'uncommon' => 'bg-green-50',
                                        'rare' => 'bg-blue-50',
                                        'epic' => 'bg-purple-50',
                                        'legendary' => 'bg-yellow-50',
                                        'mythic' => 'bg-red-50',
                                        default => 'bg-gray-100'
                                    };

                                    $iconText = match($item->type) {
                                        'weapon' => '⚔️',
                                        'armor' => '🛡️',
                                        'potion' => '🧪',
                                        'artifact' => '🔮',
                                        'accessory' => '💍',
                                        'scroll' => '📜',
                                        default => '📦'
                                    };

                                    $rarityClass = match($item->rarity) {
                                        'common' => 'bg-gray-200 text-gray-800',
                                        'uncommon' => 'bg-green-200 text-green-800',
                                        'rare' => 'bg-blue-200 text-blue-800',
                                        'epic' => 'bg-purple-200 text-purple-800',
                                        'legendary' => 'bg-yellow-200 text-yellow-800',
                                        'mythic' => 'bg-red-200 text-red-800',
                                        default => 'bg-gray-200 text-gray-800'
                                    };
                                @endphp
                                <span class="text-6xl">{{ $iconText }}</span>
                            </div>
                        </div>

                        <!-- Item Details -->
                        <div class="md:w-2/3">
                            <div class="flex items-center mb-4">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $rarityClass }}">
                                    {{ ucfirst($item->rarity) }}
                                </span>
                                <span class="ml-3 text-gray-600">{{ ucfirst($item->type) }}</span>
                            </div>

                            <p class="text-gray-700 mb-6">{{ $item->description }}</p>

                            <h3 class="text-lg font-semibold mb-2">Statistics</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="text-gray-500 text-sm">Strength</div>
                                    <div class="text-2xl font-bold">{{ $item->strength }}</div>
                                    <div class="h-2 w-full bg-gray-200 rounded-full mt-1">
                                        <div class="h-2 bg-red-500 rounded-full" style="width: {{ $item->strength }}%;"></div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="text-gray-500 text-sm">Speed</div>
                                    <div class="text-2xl font-bold">{{ $item->speed }}</div>
                                    <div class="h-2 w-full bg-gray-200 rounded-full mt-1">
                                        <div class="h-2 bg-blue-500 rounded-full" style="width: {{ $item->speed }}%;"></div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="text-gray-500 text-sm">Durability</div>
                                    <div class="text-2xl font-bold">{{ $item->durability }}</div>
                                    <div class="text-xs text-gray-500">Max: {{ $maxDurability }}</div>
                                    <div class="h-2 w-full bg-gray-200 rounded-full mt-1">
                                        <div class="h-2 bg-green-500 rounded-full" style="width: {{ $durabilityPercentage }}%;"></div>
                                    </div>
                                </div>
                            </div>

                            @if(is_array($magicProperties = json_decode($item->magic_properties, true)) && count($magicProperties) > 0)
                                <h3 class="text-lg font-semibold mb-2">Magic Properties</h3>
                                <div class="bg-indigo-50 p-4 rounded-lg mb-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($magicProperties as $property => $value)
                                            <div class="flex items-center">
                                                <span class="w-4 h-4 rounded-full bg-indigo-200 flex items-center justify-center mr-2">
                                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                                </span>
                                                <span class="text-gray-800">
                                                    {{ ucwords(str_replace('_', ' ', $property)) }}:
                                                    <span class="font-medium">+{{ $value }}</span>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
