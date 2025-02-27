<div class="bg-gray-50 p-4 mb-4 rounded-lg">
    <h4 class="text-md font-semibold mb-3">{{ $title }}</h4>

    @if($items->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($items as $tradeItem)
                @if($tradeItem->inventory && $tradeItem->inventory->item)
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h5 class="font-medium text-gray-900">{{ $tradeItem->inventory->item->name }}</h5>
                                <div class="flex items-center mt-1">
                                    <x-rarity-badge :rarity="$tradeItem->inventory->item->rarity" class="text-xs" />
                                    <span class="ml-2 text-xs text-gray-600">{{ ucfirst($tradeItem->inventory->item->type) }}</span>
                                </div>
                            </div>

                            @if($trade->isPending() && $canEdit)
                                <form method="POST" action="{{ route('trades.remove-item', ['trade' => $trade->id, 'item' => $tradeItem->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm"
                                            onclick="return confirm('Are you sure you want to remove this item from the trade?')">
                                        Remove
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="grid grid-cols-3 gap-1 mt-2 text-xs">
                            <div class="text-gray-700">STR: {{ $tradeItem->inventory->item->strength }}</div>
                            <div class="text-gray-700">SPD: {{ $tradeItem->inventory->item->speed }}</div>
                            <div class="text-gray-700">DUR: {{ $tradeItem->inventory->item->durability }}</div>
                        </div>

                        @if(is_array($magicProperties = json_decode($tradeItem->inventory->item->magic_properties, true)) && count($magicProperties) > 0)
                            <div class="mt-2 p-2 bg-indigo-50 rounded-md">
                                <span class="text-xs font-medium text-indigo-800">Magic Properties:</span>
                                <div class="flex flex-wrap gap-x-3 gap-y-1 mt-1">
                                    @foreach(array_slice($magicProperties, 0, 3) as $property => $value)
                                        <span class="text-xs text-indigo-700">
                                            {{ ucwords(str_replace('_', ' ', $property)) }}: +{{ $value }}
                                        </span>
                                    @endforeach
                                    @if(count($magicProperties) > 3)
                                        <span class="text-xs text-indigo-700">+{{ count($magicProperties) - 3 }} more</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <div class="text-red-800 font-medium">Item Unavailable</div>
                        <p class="text-xs text-red-600 mt-1">
                            This item is no longer available in the inventory.
                        </p>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <p class="text-gray-500 text-sm">No items offered.</p>
    @endif

    @if($trade->isPending() && $canAdd)
        <div class="mt-4">
            <a href="{{ route('trades.edit', $trade) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Add Items
            </a>
        </div>
    @endif
</div>
