@extends('layouts.app')

@section('title', 'Developers')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold">Developers</h1>
    <div class="flex items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="developer-search-form" class="relative">
            <input type="search" name="q" id="developer-search-input" class="block w-64 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Cari…" aria-label="Search" value="{{ request('q') }}">
            <button class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500" id="developer-search-submit" type="submit">
                🔍
            </button>
            <button class="hidden absolute right-8 top-1/2 -translate-y-1/2 text-gray-500" type="button" id="developer-search-clear">✖</button>
            <div id="developer-search-spinner" class="hidden absolute right-2 top-1/2 -translate-y-1/2">
                <svg class="h-4 w-4 animate-spin text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
            </div>
        </form>
        <x-button variant="primary" disabled>Add</x-button>
    </div>
</div>

<div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">No</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Name</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Email</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Action</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        @forelse($developers as $developer)
        <tr class="odd:bg-white even:bg-gray-50">
            <td class="px-4 py-2">{{ $developers->total() - ($developers->currentPage() - 1) * $developers->perPage() - $loop->index }}</td>
            <td class="px-4 py-2">{{ $developer->name }}</td>
            <td class="px-4 py-2">{{ $developer->email }}</td>
            <td class="px-4 py-2 space-x-2">
                <x-button href="/developer/{{ $developer->id }}/see" variant="secondary" size="sm">View</x-button>
                @if(session('user_id') == $developer->id)
                    <x-button href="/developer/{{ $developer->id }}/edit" variant="warning" size="sm">Edit</x-button>
                @else
                    <x-button variant="warning" size="sm" disabled>Edit</x-button>
                @endif
                <x-button variant="danger" size="sm" disabled>Delete</x-button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="px-4 py-2 text-center">
                @if(request('q'))
                    Tidak ada hasil untuk '{{ request('q') }}'.
                @else
                    No developers found.
                @endif
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>

<p class="mt-2 text-sm text-gray-600">Showing {{ $developers->count() }} out of {{ $developers->total() }} developers</p>

<div class="mt-4 flex justify-between items-center">
    <span class="text-sm">(Page {{ $developers->currentPage() }} of {{ $developers->lastPage() }})</span>
    <x-pagination :paginator="$developers" />
</div>

<script>
var developerSearchForm = document.getElementById('developer-search-form');
var developerSearchInput = document.getElementById('developer-search-input');
var developerSearchClear = document.getElementById('developer-search-clear');
var developerSearchSpinner = document.getElementById('developer-search-spinner');
var developerSearchTimer;

function submitDeveloperSearch(){
    developerSearchSpinner.classList.remove('hidden');
    var params = new URLSearchParams(new FormData(developerSearchForm));
    if(!developerSearchInput.value) { params.delete('q'); }
    params.delete('page');
    var query = params.toString();
    window.location = developerSearchForm.getAttribute('action') + (query ? '?' + query : '');
}

developerSearchInput.addEventListener('input', function(){
    developerSearchClear.classList.toggle('hidden', !this.value);
    clearTimeout(developerSearchTimer);
    developerSearchTimer = setTimeout(submitDeveloperSearch, 300);
});

developerSearchForm.addEventListener('submit', function(e){
    e.preventDefault();
    submitDeveloperSearch();
});

developerSearchClear.addEventListener('click', function(){
    developerSearchInput.value = '';
    submitDeveloperSearch();
});
</script>
@endsection
