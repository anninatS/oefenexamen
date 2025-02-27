<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notifications') }}
            </h2>
            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    {{ __('Mark All as Read') }}
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($notifications->count() > 0)
                        <div class="space-y-4">
                            @foreach($notifications as $notification)
                                <div class="p-4 rounded-lg border {{ $notification->read ? 'bg-gray-50' : 'bg-blue-50 border-blue-200' }} transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start space-x-3">
                                            <!-- Icon based on notification type -->
                                            @php
                                                $iconClass = match($notification->type) {
                                                    'trade_request' => 'text-indigo-500',
                                                    'trade_accepted' => 'text-green-500',
                                                    'trade_rejected' => 'text-red-500',
                                                    'item_received' => 'text-amber-500',
                                                    default => 'text-gray-500'
                                                };

                                                $iconSvg = match($notification->type) {
                                                    'trade_request' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>',
                                                    'trade_accepted' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>',
                                                    'trade_rejected' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>',
                                                    'item_received' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>',
                                                    default => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                                                };
                                            @endphp

                                            <div class="{{ $iconClass }}">
                                                {!! $iconSvg !!}
                                            </div>

                                            <div>
                                                <p class="text-sm text-gray-800">{{ $notification->message }}</p>
                                                <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>

                                        @if(!$notification->read)
                                            <form method="POST" action="{{ route('notifications.mark-read', $notification) }}">
                                                @csrf
                                                <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800">
                                                    Mark as read
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <p class="text-center text-gray-500">{{ __('You have no notifications.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
