@extends('layouts.app')

@section('title', 'Supervisors')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Supervisors</h1>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary position-relative" data-bs-toggle="offcanvas" data-bs-target="#supervisorFilter" title="Filter">
            <i class="bi bi-funnel"></i>
            @if(count($filters))
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        <a href="/supervisor/add" class="btn btn-primary">Add</a>
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
            <th>Supervisor Number</th>
            <th>Name</th>
            <th>Department</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($supervisors as $supervisor)
        <tr>
            <td>{{ $supervisor->supervisor_number }}</td>
            <td>{{ $supervisor->name }}</td>
            <td>{{ $supervisor->department }}</td>
            <td>
                <a href="/supervisor/{{ $supervisor->id }}/see" class="btn btn-sm btn-secondary">View</a>
                <a href="/supervisor/{{ $supervisor->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                <form action="/supervisor/{{ $supervisor->id }}" method="POST" style="display:inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="4">No supervisors found.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $supervisors->links() }}

<div class="offcanvas offcanvas-end" tabindex="-1" id="supervisorFilter">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="supervisor-filter-form">
            <div class="mb-3">
                <label class="form-label">Department</label>
                <input type="text" class="form-control" name="department~" value="{{ request('department~') }}">
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
                <button type="button" class="btn btn-secondary" id="supervisor-filter-reset">Reset</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('supervisor-filter-form').addEventListener('submit', function(){
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

document.getElementById('supervisor-filter-reset').addEventListener('click', function(){
    window.location = window.location.pathname;
});
</script>
@endsection

