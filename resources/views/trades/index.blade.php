<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Trade Requests') }}
            </h2>
            <a href="{{ route('trades.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                {{ __('New Trade Request') }}
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

            <!-- Received Trade Requests -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __('Received Trade Requests') }}</h3>

                    @if($receivedTradeRequests->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">From</th>
                                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="py-3 px-4 text-right text-sm font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                @foreach($receivedTradeRequests as $tradeRequest)
                                    <tr>
                                        <td class="py-3 px-4 text-sm text-gray-800">{{ $tradeRequest->sender->name }}</td>
                                        <td class="py-3 px-4 text-sm text-gray-800">
                                            {{ $tradeRequest->tradeItems->count() }} items
                                        </td>
                                        <td class="py-3 px-4 text-sm">
                                            @if($tradeRequest->isPending())
                                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded">Pending</span>
                                            @elseif($tradeRequest->isAccepted())
                                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded">Accepted</span>
                                            @elseif($tradeRequest->isRejected())
                                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded">Rejected</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-gray-800">{{ $tradeRequest->created_at->format('M j, Y H:i') }}</td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('trades.show', $tradeRequest) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>

                                            @if($tradeRequest->isPending())
                                                <form method="POST" action="{{ route('trades.update', $tradeRequest) }}" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="action" value="accept">
                                                    <button type="submit" class="text-green-600 hover:text-green-900 mr-3" onclick="return confirm('Are you sure you want to accept this trade request?')">Accept</button>
                                                </form>

                                                <form method="POST" action="{{ route('trades.update', $tradeRequest) }}" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to reject this trade request?')">Reject</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $receivedTradeRequests->appends(['sent_page' => request()->sent_page])->links() }}
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('You have no received trade requests.') }}</p>
                    @endif
                </div>
            </div>

            <!-- Sent Trade Requests -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __('Sent Trade Requests') }}</h3>

                    @if($sentTradeRequests->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">To</th>
                                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="py-3 px-4 text-right text-sm font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                @foreach($sentTradeRequests as $tradeRequest)
                                    <tr>
                                        <td class="py-3 px-4 text-sm text-gray-800">{{ $tradeRequest->receiver->name }}</td>
                                        <td class="py-3 px-4 text-sm text-gray-800">
                                            {{ $tradeRequest->tradeItems->count() }} items
                                        </td>
                                        <td class="py-3 px-4 text-sm">
                                            @if($tradeRequest->isPending())
                                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded">Pending</span>
                                            @elseif($tradeRequest->isAccepted())
                                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded">Accepted</span>
                                            @elseif($tradeRequest->isRejected())
                                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded">Rejected</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-gray-800">{{ $tradeRequest->created_at->format('M j, Y H:i') }}</td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('trades.show', $tradeRequest) }}" class="text-indigo-600 hover:text-indigo-900">View</a>

                                            @if($tradeRequest->isPending())
                                                <form method="POST" action="{{ route('trades.update', $tradeRequest) }}" class="inline ml-3">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="action" value="cancel">
                                                    <button type="submit" class="text-gray-600 hover:text-gray-900" onclick="return confirm('Are you sure you want to cancel this trade request?')">Cancel</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $sentTradeRequests->appends(['received_page' => request()->received_page])->links() }}
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('You have no sent trade requests.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
