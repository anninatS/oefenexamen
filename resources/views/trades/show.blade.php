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
                <!-- Trade Status Banner -->
                <div class="mb-6 p-4 rounded-lg
    {{ $tradeRequest->isPending() ? 'bg-yellow-100 text-yellow-800' :
       ($tradeRequest->isModified() ? 'bg-blue-100 text-blue-800' :
       ($tradeRequest->isAccepted() ? 'bg-green-100 text-green-800' :
       'bg-red-100 text-red-800')) }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium">
                                @if($tradeRequest->isPending())
                                    Trade Request Pending
                                @elseif($tradeRequest->isModified())
                                    Trade Request Modified
                                @elseif($tradeRequest->isAccepted())
                                    Trade Request Accepted
                                @else
                                    Trade Request Rejected
                                @endif
                            </h3>
                            <p class="text-sm mt-1">
                                {{ $isSender ? 'You' : $tradeRequest->sender->name }} sent a trade request to {{ $isSender ? $tradeRequest->receiver->name : 'you' }} on {{ $tradeRequest->created_at->format('M j, Y \a\t H:i') }}.

                                @if($tradeRequest->isModified())
                                    <br>
                                    <strong>
                                        {{ $tradeRequest->modifiedBy->id === Auth::id() ? 'You' : $tradeRequest->modifiedBy->name }}
                                        last modified this trade on {{ $tradeRequest->updated_at->format('M j, Y \a\t H:i') }}.
                                    </strong>
                                @endif
                            </p>
                        </div>

                        @if($tradeRequest->isActive())
                            @if($tradeRequest->canBeApprovedBy(Auth::user()))
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
                            @elseif($tradeRequest->isPending() && $isSender)
                                <!-- Cancel button for sender -->
                                <form method="POST" action="{{ route('trades.update', $tradeRequest) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="action" value="cancel">
                                    <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md" onclick="return confirm('Are you sure you want to cancel this trade request?')">
                                        Cancel Trade
                                    </button>
                                </form>
                            @elseif($tradeRequest->isModified() && $tradeRequest->modified_by_id === Auth::id())
                                <div class="flex items-center px-4 py-2 bg-blue-500 text-white rounded-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    Waiting for other party's approval
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

            <!-- Trade Items -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __('Items in this Trade') }}</h3>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Sender's Items -->
                        <div>
                            @include('trades._trade_items', [
                                'title' => 'You are offering:',
                                'items' => $senderItems,
                                'trade' => $tradeRequest,
                                'canEdit' => $tradeRequest->isPending() && $isSender,
                                'canAdd' => $tradeRequest->isPending() && $isSender
                            ])
                        </div>

                        <!-- Receiver's Items -->
                        <div>
                            @include('trades._trade_items', [
                                'title' => 'You will receive:',
                                'items' => $receiverItems,
                                'trade' => $tradeRequest,
                                'canEdit' => $tradeRequest->isPending() && !$isSender,
                                'canAdd' => $tradeRequest->isPending() && !$isSender
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
