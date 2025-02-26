@props(['rarity'])

@php
    $classes = match (strtolower($rarity)) {
        'common' => 'bg-gray-200 text-gray-800',
        'uncommon' => 'bg-green-200 text-green-800',
        'rare' => 'bg-blue-200 text-blue-800',
        'epic' => 'bg-purple-200 text-purple-800',
        'legendary' => 'bg-yellow-200 text-yellow-800',
        'mythic' => 'bg-red-200 text-red-800',
        default => 'bg-gray-200 text-gray-800',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-block px-2 py-1 text-xs font-semibold rounded ' . $classes]) }}>
    {{ ucfirst($rarity) }}
</span>
