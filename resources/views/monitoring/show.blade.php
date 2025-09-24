@extends('layouts.app')

@section('title', 'Monitoring Log Details')

@section('content')
@php($role = session('role'))
<h1>Monitoring Log Details</h1>
<div class="card mb-3">
    <div class="card-body">
        <ul class="list-unstyled mb-0">
            <li class="mb-2"><strong>Internship:</strong> <a href="/internships/{{ $log->internship_id }}/read">{{ $log->student_name }} – {{ $log->institution_name }}</a></li>
            <li class="mb-2"><strong>Date:</strong> {{ $log->log_date }}</li>
            <li class="mb-2"><strong>Supervisor:</strong> {{ $log->supervisor_name ?? '—' }}</li>
            <li class="mb-2"><strong>Type:</strong> {{ $log->log_type }}</li>
            <li class="mb-2"><strong>Title:</strong> {{ $log->title ?? '—' }}</li>
            <li><strong>Content:</strong></li>
        </ul>
        <pre class="mt-2 mb-0" style="white-space: pre-wrap;">{{ $log->content }}</pre>
    </div>
</div>
@if($role !== 'student')
    <a href="/monitoring/{{ $log->monitoring_log_id }}/edit" class="btn btn-warning">Update</a>
    <form action="/monitoring/{{ $log->monitoring_log_id }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this log?');">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">Delete</button>
    </form>
@endif
<a href="/monitoring" class="btn btn-secondary">Back</a>
@endsection
