<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Trade Request Details') }}
            </h2>
            <a href="{{ route('trades.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                {{ __('Back to Trades') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Trade Status Banner -->
            <div class="mb-6 p-4 rounded-lg {{ $tradeRequest->isPending() ? 'bg-yellow-100 text-yellow-800' : ($tradeRequest->isAccepted() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium">
                            Trade Request {{ $tradeRequest->isPending() ? 'Pending' : ($tradeRequest->isAccepted() ? 'Accepted' : 'Rejected') }}
                        </h3>
                        <p class="text-sm mt-1">
                            {{ $isSender ? 'You' : $tradeRequest->sender->name }} sent a trade request to {{ $isSender ? $tradeRequest->receiver->name : 'you' }} on {{ $tradeRequest->created_at->format('M j, Y \a\t H:i') }}.
                        </p>
                    </div>

                    @if($tradeRequest->isPending())
                        @if($isSender)
                            <!-- Cancel button for sender -->
                            <form method="POST" action="{{ route('trades.update', $tradeRequest) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md" onclick="return confirm('Are you sure you want to cancel this trade request?')">
                                    Cancel Trade
                                </button>
                            </form>
                        @else
                            <div class="flex space-x-2">
                                <form method="POST" action="{{ route('trades.update', $tradeRequest) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md" onclick="return confirm('Are you sure you want to accept this trade request?')">
                                        Accept
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('trades.update', $tradeRequest) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md" onclick="return confirm('Are you sure you want to reject this trade request?')">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Trade Items -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __('Items Being Traded') }}</h3>

                    @if($tradeRequest->tradeItems->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($tradeRequest->tradeItems as $tradeItem)
                                @if($tradeItem->inventory && $tradeItem->inventory->item)
                                    <div class="bg-white border border-gray-200 rounded-lg shadow-md">
                                        <div class="p-4">
                                            <h4 class="text-lg font-semibold text-gray-900 mb-1">{{ $tradeItem->inventory->item->name }}</h4>
                                            <div class="flex items-center mb-2">
                                                <x-rarity-badge :rarity="$tradeItem->inventory->item->rarity" />
                                                <span class="ml-2 text-sm text-gray-600">{{ ucfirst($tradeItem->inventory->item->type) }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $tradeItem->inventory->item->description }}</p>

                                            <div class="grid grid-cols-3 gap-2 mb-3">
                                                <div class="text-center">
                                                    <span class="block text-xs text-gray-500">Strength</span>
                                                    <span class="font-semibold">{{ $tradeItem->inventory->item->strength }}</span>
                                                </div>
                                                <div class="text-center">
                                                    <span class="block text-xs text-gray-500">Speed</span>
                                                    <span class="font-semibold">{{ $tradeItem->inventory->item->speed }}</span>
                                                </div>
                                                <div class="text-center">
                                                    <span class="block text-xs text-gray-500">Durability</span>
                                                    <span class="font-semibold">{{ $tradeItem->inventory->item->durability }}</span>
                                                </div>
                                            </div>

                                            @if(is_array($magicProperties = json_decode($tradeItem->inventory->item->magic_properties, true)) && count($magicProperties) > 0)
                                                <div class="bg-indigo-50 p-3 rounded-lg mb-3">
                                                    <h5 class="text-xs font-medium text-indigo-800 mb-1">Magic Properties</h5>
                                                    <div class="grid grid-cols-1 gap-1">
                                                        @foreach($magicProperties as $property => $value)
                                                            <div class="text-xs text-indigo-700">
                                                                {{ ucwords(str_replace('_', ' ', $property)) }}: +{{ $value }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="text-xs text-gray-500">
                                                Offered by: {{ $tradeRequest->sender->name }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-red-50 border border-red-200 rounded-lg shadow-md">
                                        <div class="p-4">
                                            <h4 class="text-lg font-semibold text-red-800 mb-1">Item Unavailable</h4>
                                            <p class="text-sm text-red-600 mb-3">
                                                This item is no longer available in the inventory.
                                            </p>
                                            <div class="text-xs text-red-500">
                                                The item might have been deleted or removed from the game.
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('No items included in this trade request.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
