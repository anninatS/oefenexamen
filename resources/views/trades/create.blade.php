<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Trade Request') }}
            </h2>
            <a href="{{ route('trades.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                {{ __('Back to Trades') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('trades.store') }}" id="trade-form">
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

                        <!-- Select Trade Partner -->
                        <div class="mb-6">
                            <label for="receiver_id" class="block text-sm font-medium text-gray-700 mb-2">Trade Partner</label>

                            @if($potentialReceivers->count() > 0)
                                <select id="receiver_id" name="receiver_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                                    <option value="">Select a player</option>
                                    @foreach($potentialReceivers as $receiver)
                                        <option value="{{ $receiver->id }}" {{ old('receiver_id') == $receiver->id ? 'selected' : '' }}>
                                            {{ $receiver->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <p class="text-red-500">No other players available for trading.</p>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <!-- Your Items (Sender) -->
                            <div class="border rounded-lg p-4">
                                <h3 class="text-lg font-medium mb-4">Your Items to Offer</h3>

                                @if($senderItems->count() > 0)
                                    <div class="max-h-96 overflow-y-auto p-2">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($senderItems as $inventoryItem)
                                                <div class="border rounded-lg p-4 flex items-start space-x-3">
                                                    <input type="checkbox" id="sender_item_{{ $inventoryItem->id }}" name="sender_items[]" value="{{ $inventoryItem->id }}" class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ in_array($inventoryItem->id, old('sender_items', [])) ? 'checked' : '' }}>

                                                    <div>
                                                        <label for="sender_item_{{ $inventoryItem->id }}" class="font-medium text-gray-800">{{ $inventoryItem->item->name }}</label>
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
                                    <p class="text-red-500">You don't have any items in your inventory to trade.</p>
                                @endif
                            </div>

                            <!-- Trade Partner's Items (Receiver) -->
                            <div class="border rounded-lg p-4">
                                <h3 class="text-lg font-medium mb-4">Items You Want in Return</h3>

                                <div id="receiver-items-container">
                                    <p class="text-gray-500">Please select a trade partner first to see their available items.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors" {{ ($potentialReceivers->count() === 0 || $senderItems->count() === 0) ? 'disabled' : '' }}>
                                {{ __('Send Trade Request') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const receiverSelect = document.getElementById('receiver_id');
            const receiverItemsContainer = document.getElementById('receiver-items-container');

            receiverSelect.addEventListener('change', function() {
                const receiverId = this.value;

                if (receiverId) {
                    // Show loading indicator
                    receiverItemsContainer.innerHTML = '<p class="text-gray-500">Loading items...</p>';

                    // Fetch the receiver's items
                    fetch(`/user-items?user_id=${receiverId}`)
                        .then(response => response.text())
                        .then(html => {
                            receiverItemsContainer.innerHTML = html;
                        })
                        .catch(error => {
                            console.error('Error fetching receiver items:', error);
                            receiverItemsContainer.innerHTML = '<p class="text-red-500">Error loading items. Please try again.</p>';
                        });
                } else {
                    receiverItemsContainer.innerHTML = '<p class="text-gray-500">Please select a trade partner first to see their available items.</p>';
                }
            });

            // If a receiver is already selected (e.g., on form validation error), load their items
            if (receiverSelect.value) {
                receiverSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
</x-app-layout>
