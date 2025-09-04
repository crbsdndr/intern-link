@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="flex items-center gap-2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 rounded border text-gray-300">Back</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 rounded border hover:bg-gray-50">Back</a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 rounded border hover:bg-gray-50">Next</a>
        @else
            <span class="px-3 py-1.5 rounded border text-gray-300">Next</span>
        @endif
    </nav>
@endif
