@props([
    'label' => null,
    'id' => null,
    'type' => 'text',
    'name' => null,
    'value' => null,
    'error' => null,
])

<div>
    @if($label)
        <label for="{{ $id }}" class="mb-1 block text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif
    <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" value="{{ old($name, $value) }}" {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500']) }} />
    @if($error)
        <p class="mt-1 text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
