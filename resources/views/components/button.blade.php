@props([
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
])

@php
$base = 'inline-flex items-center gap-2 rounded-lg font-medium focus:outline-none focus:ring-2 disabled:opacity-50';
$variants = [
    'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    'secondary' => 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    'warning' => 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-400',
    'outline' => 'border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500',
];
$sizes = [
    'sm' => 'px-2 py-1 text-xs',
    'md' => 'px-4 py-2 text-sm',
];
@endphp

@php
$classes = "$base {$variants[$variant] ?? $variants['primary']} {$sizes[$size] ?? $sizes['md']}";
@endphp

@if ($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
