@if($inventoryItems->count() > 0)
    <div class="max-h-96 overflow-y-auto p-2">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($inventoryItems as $inventoryItem)
                <div class="border rounded-lg p-4 flex items-start space-x-3">
                    <input type="checkbox" id="receiver_item_{{ $inventoryItem->id }}" name="receiver_items[]" value="{{ $inventoryItem->id }}" class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ in_array($inventoryItem->id, old('receiver_items', [])) ? 'checked' : '' }}>

                    <div>
                        <label for="receiver_item_{{ $inventoryItem->id }}" class="font-medium text-gray-800">{{ $inventoryItem->item->name }}</label>
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
    </div>
@else
    <p class="text-gray-500">This player doesn't have any items available for trading.</p>
@endif
