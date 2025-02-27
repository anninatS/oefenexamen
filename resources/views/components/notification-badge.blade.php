@props(['count'])

@if($count > 0)
    <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
        {{ $count > 99 ? '99+' : $count }}
    </span>
@endif
