@extends('layouts.app')

@section('title', 'Institutions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Institutions</h1>
    @php($isStudent = session('role') === 'student')
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="institution-search-form" class="position-relative">
            <div class="input-group" style="min-width:280px;">
                <input type="search" name="q" id="institution-search-input" class="form-control" placeholder="Cari…" aria-label="Search" value="{{ request('q') }}">
                <button class="btn btn-outline-secondary" type="submit" id="institution-search-submit"><i class="bi bi-search"></i></button>
                <button class="btn btn-outline-secondary" type="button" id="institution-search-clear" @if(!request('q')) style="display:none;" @endif><i class="bi bi-x"></i></button>
            </div>
            <div id="institution-search-spinner" class="position-absolute top-50 end-0 translate-middle-y me-2 d-none">
                <div class="spinner-border spinner-border-sm text-secondary"></div>
            </div>
            @foreach(request()->except('q','page') as $param => $value)
                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
            @endforeach
        </form>
        <button class="btn btn-outline-secondary position-relative" data-bs-toggle="offcanvas" data-bs-target="#institutionFilter" title="Filter">
            <i class="bi bi-funnel"></i>
            @if(count($filters))
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        @if($isStudent)
            <button class="btn btn-primary" disabled>Add</button>
        @else
            <a href="/institution/add" class="btn btn-primary">Add</a>
        @endif
    </div>
</div>

@if(count($filters))
    <div class="mb-3">
        @foreach($filters as $param => $label)
            @php($q = Arr::except(request()->query(), [$param]))
            <a href="{{ url()->current() . ($q ? '?' . http_build_query($q) : '') }}" class="badge bg-secondary text-decoration-none me-2">
                {{ $label }} <i class="bi bi-x ms-1"></i>
            </a>
        @endforeach
    </div>
@endif


<table class="table table-bordered">
    <thead>
        <tr>
            <th>No</th>
            <th>Name</th>
            <th>City</th>
            <th>Province</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($institutions as $institution)
        <tr>
            <td>{{ $institutions->total() - ($institutions->currentPage() - 1) * $institutions->perPage() - $loop->index }}</td>
            <td>{{ $institution->name }}</td>
            <td>{{ $institution->city }}</td>
            <td>{{ $institution->province }}</td>
            <td>
                <a href="/institution/{{ $institution->id }}/see" class="btn btn-sm btn-secondary">View</a>
                @if($isStudent)
                    <button class="btn btn-sm btn-warning" disabled>Edit</button>
                    <button class="btn btn-sm btn-danger" disabled>Delete</button>
                @else
                    <a href="/institution/{{ $institution->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                    <form action="/institution/{{ $institution->id }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">
                @if(request('q'))
                    Tidak ada hasil untuk '{{ request('q') }}'.
                @else
                    No institutions found.
                @endif
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<p class="text-muted">Total: {{ $institutions->total() }} results</p>

<div class="d-flex justify-content-between align-items-center">
    <span>(Page {{ $institutions->currentPage() }} of {{ $institutions->lastPage() }})</span>
    <div class="d-flex gap-2">
        @if ($institutions->onFirstPage())
            <span class="text-muted">Back</span>
        @else
            <a href="{{ $institutions->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
        @endif
        @if ($institutions->hasMorePages())
            <a href="{{ $institutions->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
        @else
            <span class="text-muted">Next</span>
        @endif
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="institutionFilter">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="institution-filter-form">
            <div class="mb-3">
                <label class="form-label">City</label>
                <input type="text" class="form-control" name="city~" value="{{ request('city~') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Province</label>
                <input type="text" class="form-control" name="province~" value="{{ request('province~') }}">
            </div>
            @php($createdRange = request('created_at'))
            @php($createdStart = $createdEnd = '')
            @if($createdRange && str_starts_with($createdRange, 'range:'))
                @php([$createdStart, $createdEnd] = array_pad(explode(',', substr($createdRange, 6)), 2, ''))
            @endif
            <div class="mb-3">
                <label class="form-label">Created At</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="created_at_start" value="{{ $createdStart }}">
                    <input type="date" class="form-control" id="created_at_end" value="{{ $createdEnd }}">
                </div>
                <input type="hidden" name="created_at" id="created_at_range">
            </div>
            @php($updatedRange = request('updated_at'))
            @php($updatedStart = $updatedEnd = '')
            @if($updatedRange && str_starts_with($updatedRange, 'range:'))
                @php([$updatedStart, $updatedEnd] = array_pad(explode(',', substr($updatedRange, 6)), 2, ''))
            @endif
            <div class="mb-3">
                <label class="form-label">Updated At</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="updated_at_start" value="{{ $updatedStart }}">
                    <input type="date" class="form-control" id="updated_at_end" value="{{ $updatedEnd }}">
                </div>
                <input type="hidden" name="updated_at" id="updated_at_range">
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary" id="institution-filter-reset">Reset</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
var institutionSearchForm = document.getElementById('institution-search-form');
var institutionSearchInput = document.getElementById('institution-search-input');
var institutionSearchClear = document.getElementById('institution-search-clear');
var institutionSearchSpinner = document.getElementById('institution-search-spinner');
var institutionSearchTimer;

function submitInstitutionSearch(){
    institutionSearchSpinner.classList.remove('d-none');
    var params = new URLSearchParams(new FormData(institutionSearchForm));
    if(!institutionSearchInput.value) { params.delete('q'); }
    params.delete('page');
    var query = params.toString();
    window.location = institutionSearchForm.getAttribute('action') + (query ? '?' + query : '');
}

institutionSearchInput.addEventListener('input', function(){
    institutionSearchClear.style.display = this.value ? 'block' : 'none';
    clearTimeout(institutionSearchTimer);
    institutionSearchTimer = setTimeout(submitInstitutionSearch, 300);
});

institutionSearchForm.addEventListener('submit', function(e){
    e.preventDefault();
    submitInstitutionSearch();
});

institutionSearchClear.addEventListener('click', function(){
    institutionSearchInput.value = '';
    submitInstitutionSearch();
});

document.getElementById('institution-filter-form').addEventListener('submit', function(){
    var cs = document.getElementById('created_at_start').value;
    var ce = document.getElementById('created_at_end').value;
    var ch = document.getElementById('created_at_range');
    if(cs || ce){
        ch.value = 'range:' + cs + ',' + ce;
    }else{
        ch.disabled = true;
    }

    var us = document.getElementById('updated_at_start').value;
    var ue = document.getElementById('updated_at_end').value;
    var uh = document.getElementById('updated_at_range');
    if(us || ue){
        uh.value = 'range:' + us + ',' + ue;
    }else{
        uh.disabled = true;
    }
});

document.getElementById('institution-filter-reset').addEventListener('click', function(){
    window.location = institutionSearchForm.getAttribute('action');
});
</script>
@endsection

