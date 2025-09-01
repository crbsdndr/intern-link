@extends('layouts.app')

@php use Illuminate\Support\Arr; @endphp

@section('title', 'Internships')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Internships</h1>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary position-relative" data-bs-toggle="offcanvas" data-bs-target="#internshipFilter" title="Filter">
            <i class="bi bi-funnel"></i>
            @if(count($filters))
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        <a href="/internship/add" class="btn btn-primary">Add</a>
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
            <th>Student Name</th>
            <th>Institution Name</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($internships as $internship)
        <tr>
            <td>{{ $internship->student_name }}</td>
            <td>{{ $internship->institution_name }}</td>
            <td>{{ $internship->start_date }}</td>
            <td>{{ $internship->end_date }}</td>
            <td>
                <a href="/internship/{{ $internship->id }}/see" class="btn btn-sm btn-secondary">View</a>
                <a href="/internship/{{ $internship->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                <form action="/internship/{{ $internship->id }}" method="POST" style="display:inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5">No internships found.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $internships->links() }}

<div class="offcanvas offcanvas-end" tabindex="-1" id="internshipFilter">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="internship-filter-form">
            @php($statusValues = [])
            @if(request()->has('status') && str_starts_with(request('status'), 'in:'))
                @php($statusValues = explode(',', substr(request('status'), 3)))
            @endif
            <div class="mb-3">
                <label class="form-label">Status</label>
                @foreach(['planned','ongoing','completed','terminated'] as $s)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="{{ $s }}" id="status-{{ $s }}" @if(in_array($s,$statusValues)) checked @endif>
                        <label class="form-check-label" for="status-{{ $s }}">{{ ucwords($s) }}</label>
                    </div>
                @endforeach
                <input type="hidden" name="status" id="status-hidden">
            </div>
            @php($startRange = request('start_date'))
            @php($startStart = $startEnd = '')
            @if($startRange && str_starts_with($startRange, 'range:'))
                @php([$startStart, $startEnd] = array_pad(explode(',', substr($startRange, 6)), 2, ''))
            @endif
            <div class="mb-3">
                <label class="form-label">Start Date</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="start_date_start" value="{{ $startStart }}">
                    <input type="date" class="form-control" id="start_date_end" value="{{ $startEnd }}">
                </div>
                <input type="hidden" name="start_date" id="start_date_range">
            </div>
            @php($endRange = request('end_date'))
            @php($endStart = $endEnd = '')
            @if($endRange && str_starts_with($endRange, 'range:'))
                @php([$endStart, $endEnd] = array_pad(explode(',', substr($endRange, 6)), 2, ''))
            @endif
            <div class="mb-3">
                <label class="form-label">End Date</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="end_date_start" value="{{ $endStart }}">
                    <input type="date" class="form-control" id="end_date_end" value="{{ $endEnd }}">
                </div>
                <input type="hidden" name="end_date" id="end_date_range">
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
                <button type="button" class="btn btn-secondary" id="internship-filter-reset">Reset</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('internship-filter-form').addEventListener('submit', function(){
    var statusChecked = Array.from(document.querySelectorAll('input[id^="status-"]:checked')).map(cb => cb.value);
    var sh = document.getElementById('status-hidden');
    if(statusChecked.length){
        sh.value = 'in:' + statusChecked.join(',');
    }else{
        sh.disabled = true;
    }

    var ss = document.getElementById('start_date_start').value;
    var se = document.getElementById('start_date_end').value;
    var sr = document.getElementById('start_date_range');
    if(ss || se){
        sr.value = 'range:' + ss + ',' + se;
    }else{
        sr.disabled = true;
    }

    var es = document.getElementById('end_date_start').value;
    var ee = document.getElementById('end_date_end').value;
    var er = document.getElementById('end_date_range');
    if(es || ee){
        er.value = 'range:' + es + ',' + ee;
    }else{
        er.disabled = true;
    }

    var cs = document.getElementById('created_at_start').value;
    var ce = document.getElementById('created_at_end').value;
    var cr = document.getElementById('created_at_range');
    if(cs || ce){
        cr.value = 'range:' + cs + ',' + ce;
    }else{
        cr.disabled = true;
    }

    var us = document.getElementById('updated_at_start').value;
    var ue = document.getElementById('updated_at_end').value;
    var ur = document.getElementById('updated_at_range');
    if(us || ue){
        ur.value = 'range:' + us + ',' + ue;
    }else{
        ur.disabled = true;
    }
});

document.getElementById('internship-filter-reset').addEventListener('click', function(){
    window.location = window.location.pathname;
});
</script>
@endsection
