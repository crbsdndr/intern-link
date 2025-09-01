@extends('layouts.app')

@php use Illuminate\Support\Str; use Illuminate\Support\Arr; @endphp

@section('title', 'Monitoring Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Monitoring Logs</h1>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary position-relative" data-bs-toggle="offcanvas" data-bs-target="#monitoringFilter" title="Filter">
            <i class="bi bi-funnel"></i>
            @if(count($filters))
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        <a href="/monitoring/add" class="btn btn-primary">Add</a>
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
            <th>Date</th>
            <th>Student</th>
            <th>Institution</th>
            <th>Supervisor</th>
            <th>Type</th>
            <th>Score</th>
            <th>Title</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($logs as $log)
        <tr>
            <td>{{ $log->log_date }}</td>
            <td>{{ $log->student_name }}</td>
            <td>{{ $log->institution_name }}</td>
            <td>{{ $log->supervisor_name ?? '—' }}</td>
            <td>{{ $log->log_type }}</td>
            <td>{{ $log->score ?? '—' }}</td>
            <td>{{ $log->title ?? Str::limit($log->content, 20) }}</td>
            <td>
                <a href="/monitoring/{{ $log->id }}/see" class="btn btn-sm btn-secondary">View</a>
                <a href="/monitoring/{{ $log->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                <form action="/monitoring/{{ $log->id }}" method="POST" style="display:inline-block" onsubmit="return confirm('Delete this log?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="8">No monitoring logs found.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $logs->links() }}

<div class="offcanvas offcanvas-end" tabindex="-1" id="monitoringFilter">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="monitoring-filter-form">
            @php($logRange = request('log_date'))
            @php($logStart = $logEnd = '')
            @if($logRange && str_starts_with($logRange, 'range:'))
                @php([$logStart, $logEnd] = array_pad(explode(',', substr($logRange, 6)), 2, ''))
            @endif
            <div class="mb-3">
                <label class="form-label">Log Date</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="log_date_start" value="{{ $logStart }}">
                    <input type="date" class="form-control" id="log_date_end" value="{{ $logEnd }}">
                </div>
                <input type="hidden" name="log_date" id="log_date_range">
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
                <button type="button" class="btn btn-secondary" id="monitoring-filter-reset">Reset</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('monitoring-filter-form').addEventListener('submit', function(){
    var ls = document.getElementById('log_date_start').value;
    var le = document.getElementById('log_date_end').value;
    var lr = document.getElementById('log_date_range');
    if(ls || le){
        lr.value = 'range:' + ls + ',' + le;
    }else{
        lr.disabled = true;
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

document.getElementById('monitoring-filter-reset').addEventListener('click', function(){
    window.location = window.location.pathname;
});
</script>
@endsection
