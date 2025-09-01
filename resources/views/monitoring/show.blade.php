@extends('layouts.app')

@section('title', 'Monitoring Log Detail')

@section('content')
<h1><a href="/internship/{{ $log->internship_id }}/see">{{ $log->student_name }} – {{ $log->institution_name }}</a></h1>
<ul class="list-unstyled">
    <li><strong>Date:</strong> {{ $log->log_date }}</li>
    <li><strong>Supervisor:</strong> {{ $log->supervisor_name ?? '—' }}</li>
    <li><strong>Type:</strong> {{ $log->log_type }}</li>
    <li><strong>Score:</strong> {{ $log->score ?? '—' }}</li>
    <li><strong>Title:</strong> {{ $log->title ?? '—' }}</li>
</ul>
<div class="mb-3">
    <strong>Isi Laporan:</strong>
    <pre style="white-space: pre-wrap;">{{ $log->content }}</pre>
</div>
<a href="/monitoring/{{ $log->monitoring_log_id }}/edit" class="btn btn-warning">Edit</a>
<form action="/monitoring/{{ $log->monitoring_log_id }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this log?')">
    @csrf
    @method('DELETE')
    <button class="btn btn-danger" type="submit">Delete</button>
</form>
<a href="/monitoring" class="btn btn-secondary">Back</a>
@endsection
