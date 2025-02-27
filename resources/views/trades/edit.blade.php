<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add Items to Trade') }}
            </h2>
            <a href="{{ route('trades.show', $tradeRequest) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                {{ __('Back to Trade Details') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('trades.add-items', $tradeRequest) }}">
                        @csrf

                        <!-- Errors -->
                        @if ($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <h3 class="text-lg font-medium mb-4">
                            {{ __('Select additional items to add to the trade') }}
                        </h3>

                        <!-- Select Items for Trade -->
                        <div class="mb-6">
                            @if($userItems->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($userItems as $inventoryItem)
                                        <div class="border rounded-lg p-4 flex items-start space-x-3">
                                            <input type="checkbox" id="item_{{ $inventoryItem->id }}" name="inventory_items[]" value="{{ $inventoryItem->id }}" class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ in_array($inventoryItem->id, old('inventory_items', [])) ? 'checked' : '' }}>

                                            <div>
                                                <label for="item_{{ $inventoryItem->id }}" class="font-medium text-gray-800">{{ $inventoryItem->item->name }}</label>
                                                <div class="flex items-center mt-1">
                                                    <x-rarity-badge :rarity="$inventoryItem->item->rarity" />
                                                    <span class="ml-2 text-sm text-gray-600">{{ ucfirst($inventoryItem->item->type) }}</span>
                                                </div>
                                                <div class="grid grid-cols-3 gap-2 mt-2 text-xs text-gray-600">
                                                    <div>STR: {{ $inventoryItem->item->strength }}</div>
                                                    <div>SPD: {{ $inventoryItem->item->speed }}</div>
                                                    <div>DUR: {{ $inventoryItem->item->durability }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-red-500">You don't have any additional items in your inventory to add to this trade.</p>
                            @endif
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors" {{ ($userItems->count() === 0) ? 'disabled' : '' }}>
                                {{ __('Add Selected Items to Trade') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
