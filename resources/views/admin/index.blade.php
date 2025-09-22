@extends('layouts.app')

@section('title', 'Admins')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Admins</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="admin-search-form" class="position-relative">
            <div class="input-group" style="min-width:280px;">
                <input type="search" name="q" id="admin-search-input" class="form-control" placeholder="Cari…" aria-label="Search" value="{{ request('q') }}">
                <button class="btn btn-outline-secondary" type="submit" id="admin-search-submit"><i class="bi bi-search"></i></button>
                <button class="btn btn-outline-secondary" type="button" id="admin-search-clear" @if(!request('q')) style="display:none;" @endif><i class="bi bi-x"></i></button>
            </div>
            <div id="admin-search-spinner" class="position-absolute top-50 end-0 translate-middle-y me-2 d-none">
                <div class="spinner-border spinner-border-sm text-secondary"></div>
            </div>
        </form>
        @if(session('role') === 'developer')
        <a class="btn btn-primary" href="/admin/add">Add</a>
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
            <th>Role</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($admins as $admin)
        <tr>
            <td>{{ $admins->total() - ($admins->currentPage() - 1) * $admins->perPage() - $loop->index }}</td>
            <td>{{ $admin->name }}</td>
            <td>{{ $admin->email }}</td>
            <td>{{ $admin->role }}</td>
            <td>{{ $admin->created_at }}</td>
            <td>{{ $admin->updated_at }}</td>
            <td>
                <a href="/admin/{{ $admin->id }}/see" class="btn btn-sm btn-secondary">View</a>
                <a href="/admin/{{ $admin->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                <form action="/admin/{{ $admin->id }}" method="POST" style="display:inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center">
                @if(request('q'))
                    Tidak ada hasil untuk '{{ request('q') }}'.
                @else
                    No admins found.
                @endif
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<p class="text-muted">Total: {{ $admins->total() }} results</p>

<div class="d-flex justify-content-between align-items-center">
    <span>(Page {{ $admins->currentPage() }} of {{ $admins->lastPage() }})</span>
    <div class="d-flex gap-2">
        @if ($admins->onFirstPage())
            <span class="text-muted">Back</span>
        @else
            <a href="{{ $admins->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
        @endif
        @if ($admins->hasMorePages())
            <a href="{{ $admins->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
        @else
            <span class="text-muted">Next</span>
        @endif
    </div>
</div>

<script>
var adminSearchForm = document.getElementById('admin-search-form');
var adminSearchInput = document.getElementById('admin-search-input');
var adminSearchClear = document.getElementById('admin-search-clear');
var adminSearchSpinner = document.getElementById('admin-search-spinner');
var adminSearchTimer;

function submitAdminSearch(){
    adminSearchSpinner.classList.remove('d-none');
    var params = new URLSearchParams(new FormData(adminSearchForm));
    if(!adminSearchInput.value) { params.delete('q'); }
    params.delete('page');
    var query = params.toString();
    window.location = adminSearchForm.getAttribute('action') + (query ? '?' + query : '');
}

adminSearchInput.addEventListener('input', function(){
    adminSearchClear.style.display = this.value ? 'block' : 'none';
    clearTimeout(adminSearchTimer);
    adminSearchTimer = setTimeout(submitAdminSearch, 300);
});

adminSearchForm.addEventListener('submit', function(e){
    e.preventDefault();
    submitAdminSearch();
});

adminSearchClear.addEventListener('click', function(){
    adminSearchInput.value = '';
    submitAdminSearch();
});
</script>
@endsection
