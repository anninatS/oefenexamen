@props(['inventoryItem'])

<div class="bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow">
    <div class="p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $inventoryItem->item->name }}</h3>
        <div class="flex items-center mb-2">
            <x-rarity-badge :rarity="$inventoryItem->item->rarity" />
            <span class="ml-2 text-sm text-gray-600">{{ ucfirst($inventoryItem->item->type) }}</span>
        </div>
        <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $inventoryItem->item->description }}</p>

        <div class="grid grid-cols-3 gap-2 mb-3">
            <div class="text-center">
                <span class="block text-xs text-gray-500">Strength</span>
                <span class="font-semibold">{{ $inventoryItem->item->strength }}</span>
            </div>
            <div class="text-center">
                <span class="block text-xs text-gray-500">Speed</span>
                <span class="font-semibold">{{ $inventoryItem->item->speed }}</span>
            </div>
            <div class="text-center">
                <span class="block text-xs text-gray-500">Durability</span>
                <span class="font-semibold">{{ $inventoryItem->item->durability }}</span>
            </div>
        </div>

        <div class="text-sm text-gray-600 mb-3">
            <span>Acquired: {{ date('M j, Y', $inventoryItem->acquired_at) }}</span>
        </div>

        <a href="{{ route('inventory.show', $inventoryItem) }}" class="block w-full text-center py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
            View Details
        </a>
    </div>
</div>
