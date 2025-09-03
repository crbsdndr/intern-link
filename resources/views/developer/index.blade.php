@extends('layouts.app')

@section('title', 'Developers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Developers</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="developer-search-form" class="position-relative">
            <div class="input-group" style="min-width:280px;">
                <input type="search" name="q" id="developer-search-input" class="form-control" placeholder="Cari…" aria-label="Search" value="{{ request('q') }}">
                <button class="btn btn-outline-secondary" type="submit" id="developer-search-submit"><i class="bi bi-search"></i></button>
                <button class="btn btn-outline-secondary" type="button" id="developer-search-clear" @if(!request('q')) style="display:none;" @endif><i class="bi bi-x"></i></button>
            </div>
            <div id="developer-search-spinner" class="position-absolute top-50 end-0 translate-middle-y me-2 d-none">
                <div class="spinner-border spinner-border-sm text-secondary"></div>
            </div>
        </form>
        @if(session('role') === 'admin')
            <a href="/developer/add" class="btn btn-primary">Add</a>
        @else
            <button class="btn btn-primary" disabled>Add</button>
        @endif
    </div>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($developers as $developer)
        <tr>
            <td>{{ $developers->total() - ($developers->currentPage() - 1) * $developers->perPage() - $loop->index }}</td>
            <td>{{ $developer->name }}</td>
            <td>{{ $developer->email }}</td>
            <td>
                <a href="/developer/{{ $developer->id }}/see" class="btn btn-sm btn-secondary">View</a>
                @if(session('role') === 'admin' || session('user_id') == $developer->id)
                    <a href="/developer/{{ $developer->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                @else
                    <button class="btn btn-sm btn-warning" disabled>Edit</button>
                @endif
                @if(session('role') === 'admin')
                    <form action="/developer/{{ $developer->id }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                @else
                    <button class="btn btn-sm btn-danger" disabled>Delete</button>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">
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

<p class="text-muted">Showing {{ $developers->count() }} out of {{ $developers->total() }} developers</p>

<div class="d-flex justify-content-between align-items-center">
    <span>(Page {{ $developers->currentPage() }} of {{ $developers->lastPage() }})</span>
    <div class="d-flex gap-2">
        @if ($developers->onFirstPage())
            <span class="text-muted">Back</span>
        @else
            <a href="{{ $developers->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
        @endif
        @if ($developers->hasMorePages())
            <a href="{{ $developers->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
        @else
            <span class="text-muted">Next</span>
        @endif
    </div>
</div>

<script>
var developerSearchForm = document.getElementById('developer-search-form');
var developerSearchInput = document.getElementById('developer-search-input');
var developerSearchClear = document.getElementById('developer-search-clear');
var developerSearchSpinner = document.getElementById('developer-search-spinner');
var developerSearchTimer;

function submitDeveloperSearch(){
    developerSearchSpinner.classList.remove('d-none');
    var params = new URLSearchParams(new FormData(developerSearchForm));
    if(!developerSearchInput.value) { params.delete('q'); }
    params.delete('page');
    var query = params.toString();
    window.location = developerSearchForm.getAttribute('action') + (query ? '?' + query : '');
}

developerSearchInput.addEventListener('input', function(){
    developerSearchClear.style.display = this.value ? 'block' : 'none';
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
