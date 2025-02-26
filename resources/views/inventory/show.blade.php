<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $inventory->item->name }}
            </h2>
            <a href="{{ route('inventory.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                Back to Inventory
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
                            <div class="aspect-square bg-gray-100 rounded-lg flex items-center justify-center">
                                @php
                                    $iconText = match($inventory->item->type) {
                                        'weapon' => '⚔️',
                                        'armor' => '🛡️',
                                        'potion' => '🧪',
                                        'artifact' => '🔮',
                                        'accessory' => '💍',
                                        'scroll' => '📜',
                                        default => '📦'
                                    };
                                @endphp
                                <span class="text-6xl">{{ $iconText }}</span>
                            </div>

                            <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold mb-2">Ownership Details</h3>
                                <div class="text-sm">
                                    <p class="mb-2"><span class="font-medium">Acquired:</span> {{ date('F j, Y', $inventory->acquired_at) }}</p>
                                    <p class="mb-2"><span class="font-medium">Days Owned:</span> {{ floor((time() - $inventory->acquired_at) / 86400) }} days</p>
                                </div>
                            </div>
                        </div>

                        <!-- Item Details -->
                        <div class="md:w-2/3">
                            <div class="flex items-center mb-4">
                                <x-rarity-badge :rarity="$inventory->item->rarity" class="px-3 py-1 text-sm" />
                                <span class="ml-3 text-gray-600">{{ ucfirst($inventory->item->type) }}</span>
                            </div>

                            <p class="text-gray-700 mb-6">{{ $inventory->item->description }}</p>

                            <h3 class="text-lg font-semibold mb-2">Statistics</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="text-gray-500 text-sm">Strength</div>
                                    <div class="text-2xl font-bold">{{ $inventory->item->strength }}</div>
                                    <div class="h-2 w-full bg-gray-200 rounded-full mt-1">
                                        <div class="h-2 bg-red-500 rounded-full" style="width: {{ $inventory->item->strength }}%;"></div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="text-gray-500 text-sm">Speed</div>
                                    <div class="text-2xl font-bold">{{ $inventory->item->speed }}</div>
                                    <div class="h-2 w-full bg-gray-200 rounded-full mt-1">
                                        <div class="h-2 bg-blue-500 rounded-full" style="width: {{ $inventory->item->speed }}%;"></div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="text-gray-500 text-sm">Durability</div>
                                    <div class="text-2xl font-bold">{{ $inventory->item->durability }}</div>
                                    <div class="h-2 w-full bg-gray-200 rounded-full mt-1">
                                        <div class="h-2 bg-green-500 rounded-full" style="width: {{ min(100, $inventory->item->durability) }}%;"></div>
                                    </div>
                                </div>
                            </div>

                            @if(is_array($magicProperties = json_decode($inventory->item->magic_properties, true)) && count($magicProperties) > 0)
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
