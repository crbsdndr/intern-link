@extends('layouts.app')

@php use Illuminate\Support\Arr; @endphp

@section('title', 'Applications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Applications</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" id="application-search-form" class="d-flex">
            <div class="input-group">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari…" id="application-search-input" style="min-width:280px">
                @foreach(request()->query() as $param => $value)
                    @if($param !== 'q' && $param !== 'page' && $param !== 'page_size')
                        <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                    @endif
                @endforeach
                <button class="btn btn-outline-secondary" type="submit" id="application-search-btn">
                    <i class="bi bi-search"></i>
                    <span class="spinner-border spinner-border-sm d-none" id="application-search-spinner"></span>
                </button>
                @if(request('q'))
                <button class="btn btn-outline-secondary" type="button" id="application-search-clear"><i class="bi bi-x"></i></button>
                @endif
            </div>
        </form>
        <button class="btn btn-outline-secondary position-relative" data-bs-toggle="offcanvas" data-bs-target="#applicationFilter" title="Filter">
            <i class="bi bi-funnel"></i>
            @if(count($filters))
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        <a href="/application/add" class="btn btn-primary">Add</a>
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
            <th>Student Name</th>
            <th>Institution Name</th>
            <th>Year</th>
            <th>Term</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($applications as $application)
        <tr>
            <td>{{ $applications->total() - ($applications->currentPage() - 1) * $applications->perPage() - $loop->index }}</td>
            <td>{{ $application->student_name }}</td>
            <td>{{ $application->institution_name }}</td>
            <td>{{ $application->period_year }}</td>
            <td>{{ $application->period_term }}</td>
            <td>
                <a href="/application/{{ $application->id }}/see" class="btn btn-sm btn-secondary">View</a>
                <a href="/application/{{ $application->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                <form action="/application/{{ $application->id }}" method="POST" style="display:inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        @if(request('q'))
        <tr><td colspan="6">Tidak ada hasil untuk '{{ request('q') }}'.</td></tr>
        @else
        <tr><td colspan="6">No applications found.</td></tr>
        @endif
        @endforelse
    </tbody>
</table>

<p class="text-muted">Total: {{ $applications->total() }} results</p>

<div class="d-flex justify-content-between align-items-center">
    <span>(Page {{ $applications->currentPage() }} of {{ $applications->lastPage() }})</span>
    <div class="d-flex gap-2">
        @if ($applications->onFirstPage())
            <span class="text-muted">Back</span>
        @else
            <a href="{{ $applications->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
        @endif
        @if ($applications->hasMorePages())
            <a href="{{ $applications->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
        @else
            <span class="text-muted">Next</span>
        @endif
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="applicationFilter">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="application-filter-form">
            <input type="hidden" name="q" value="{{ request('q') }}">
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            @php($statusValues = [])
            @if(request()->has('status') && str_starts_with(request('status'), 'in:'))
                @php($statusValues = explode(',', substr(request('status'), 3)))
            @endif
            <div class="mb-3">
                <label class="form-label">Status</label>
                @foreach(['submitted','under_review','accepted','rejected','cancelled'] as $s)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="{{ $s }}" id="status-{{ $s }}" @if(in_array($s,$statusValues)) checked @endif>
                        <label class="form-check-label" for="status-{{ $s }}">{{ ucwords(str_replace('_',' ',$s)) }}</label>
                    </div>
                @endforeach
                <input type="hidden" name="status" id="status-hidden">
            </div>
            @php($submittedRange = request('submitted_at'))
            @php($submittedStart = $submittedEnd = '')
            @if($submittedRange && str_starts_with($submittedRange, 'range:'))
                @php([$submittedStart, $submittedEnd] = array_pad(explode(',', substr($submittedRange, 6)), 2, ''))
            @endif
            <div class="mb-3">
                <label class="form-label">Submitted At</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="submitted_at_start" value="{{ $submittedStart }}">
                    <input type="date" class="form-control" id="submitted_at_end" value="{{ $submittedEnd }}">
                </div>
                <input type="hidden" name="submitted_at" id="submitted_at_range">
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
                <button type="button" class="btn btn-secondary" id="application-filter-reset">Reset</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

@php($retain = Arr::only(request()->query(), ['q','sort']))

<script>
document.getElementById('application-filter-form').addEventListener('submit', function(){
    var statusChecked = Array.from(document.querySelectorAll('input[id^="status-"]:checked')).map(cb => cb.value);
    var sh = document.getElementById('status-hidden');
    if(statusChecked.length){
        sh.value = 'in:' + statusChecked.join(',');
    }else{
        sh.disabled = true;
    }

    var ss = document.getElementById('submitted_at_start').value;
    var se = document.getElementById('submitted_at_end').value;
    var sr = document.getElementById('submitted_at_range');
    if(ss || se){
        sr.value = 'range:' + ss + ',' + se;
    }else{
        sr.disabled = true;
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

document.getElementById('application-filter-reset').addEventListener('click', function(){
    window.location = window.location.pathname + '{{ $retain ? '?' . http_build_query($retain) : '' }}';
});

var asForm = document.getElementById('application-search-form');
if(asForm){
    var asInput = document.getElementById('application-search-input');
    var asBtn = document.getElementById('application-search-btn');
    var asSpinner = document.getElementById('application-search-spinner');
    var debounceTimer;
    asInput.addEventListener('input', function(){
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function(){
            asForm.submit();
        }, 300);
    });
    asForm.addEventListener('submit', function(){
        asBtn.disabled = true;
        if(asSpinner) asSpinner.classList.remove('d-none');
    });
    var asClear = document.getElementById('application-search-clear');
    if(asClear){
        asClear.addEventListener('click', function(){
            var params = new URLSearchParams(window.location.search);
            params.delete('q');
            params.delete('page');
            window.location = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        });
    }
}
</script>
@endsection
