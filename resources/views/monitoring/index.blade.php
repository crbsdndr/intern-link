@extends('layouts.app')

@php use Illuminate\Support\Str; use Illuminate\Support\Arr; @endphp

@section('title', 'Monitoring Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Monitoring Logs</h1>
    @php($isStudent = session('role') === 'student')
    <div class="d-flex align-items-center gap-2">
        <form method="get" id="monitoring-search-form" class="d-flex">
            <div class="input-group">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari…" id="monitoring-search-input" style="min-width:280px">
                @foreach(request()->query() as $param => $value)
                    @if($param !== 'q' && $param !== 'page' && $param !== 'page_size')
                        <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                    @endif
                @endforeach
                <button class="btn btn-outline-secondary" type="submit" id="monitoring-search-btn">
                    <i class="bi bi-search"></i>
                    <span class="spinner-border spinner-border-sm d-none" id="monitoring-search-spinner"></span>
                </button>
                @if(request('q'))
                <button class="btn btn-outline-secondary" type="button" id="monitoring-search-clear"><i class="bi bi-x"></i></button>
                @endif
            </div>
        </form>
        <button class="btn btn-outline-secondary position-relative" data-bs-toggle="offcanvas" data-bs-target="#monitoringFilter" title="Filter">
            <i class="bi bi-funnel"></i>
            @if(count($filters))
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        @if($isStudent)
            <button class="btn btn-primary" disabled>Add</button>
        @else
            <a href="/monitoring/add" class="btn btn-primary">Add</a>
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
            <td>{{ $logs->total() - ($logs->currentPage() - 1) * $logs->perPage() - $loop->index }}</td>
            <td>{{ $log->log_date }}</td>
            <td>{{ $log->student_name }}</td>
            <td>{{ $log->institution_name }}</td>
            <td>{{ $log->supervisor_name ?? '—' }}</td>
            <td>{{ $log->log_type }}</td>
            <td>{{ $log->score ?? '—' }}</td>
            <td>{{ $log->title ?? Str::limit($log->content, 20) }}</td>
            <td>
                <a href="/monitoring/{{ $log->id }}/see" class="btn btn-sm btn-secondary">View</a>
                @if($isStudent)
                    <button class="btn btn-sm btn-warning" disabled>Edit</button>
                    <button class="btn btn-sm btn-danger" disabled>Delete</button>
                @else
                    <a href="/monitoring/{{ $log->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                    <form action="/monitoring/{{ $log->id }}" method="POST" style="display:inline-block" onsubmit="return confirm('Delete this log?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                @endif
            </td>
        </tr>
        @empty
        @if(request('q'))
        <tr><td colspan="9">Tidak ada hasil untuk '{{ request('q') }}'.</td></tr>
        @else
        <tr><td colspan="9">No monitoring logs found.</td></tr>
        @endif
        @endforelse
    </tbody>
</table>

<p class="text-muted">Total: {{ $logs->total() }} results</p>

<div class="d-flex justify-content-between align-items-center">
    <span>(Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }})</span>
    <div class="d-flex gap-2">
        @if ($logs->onFirstPage())
            <span class="text-muted">Back</span>
        @else
            <a href="{{ $logs->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
        @endif
        @if ($logs->hasMorePages())
            <a href="{{ $logs->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
        @else
            <span class="text-muted">Next</span>
        @endif
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="monitoringFilter">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="monitoring-filter-form">
            <input type="hidden" name="q" value="{{ request('q') }}">
            <input type="hidden" name="sort" value="{{ request('sort') }}">
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

@php($retain = Arr::only(request()->query(), ['q','sort']))

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
    window.location = window.location.pathname + '{{ $retain ? '?' . http_build_query($retain) : '' }}';
});

var msForm = document.getElementById('monitoring-search-form');
if(msForm){
    var msInput = document.getElementById('monitoring-search-input');
    var msBtn = document.getElementById('monitoring-search-btn');
    var msSpinner = document.getElementById('monitoring-search-spinner');
    var debounceTimer;
    msInput.addEventListener('input', function(){
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function(){
            msForm.submit();
        }, 300);
    });
    msForm.addEventListener('submit', function(){
        msBtn.disabled = true;
        if(msSpinner) msSpinner.classList.remove('d-none');
    });
    var msClear = document.getElementById('monitoring-search-clear');
    if(msClear){
        msClear.addEventListener('click', function(){
            var params = new URLSearchParams(window.location.search);
            params.delete('q');
            params.delete('page');
            window.location = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        });
    }
}
</script>
@endsection
