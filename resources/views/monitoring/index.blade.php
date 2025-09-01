@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('title', 'Monitoring Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Monitoring Logs</h1>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary" title="Filter"><i class="bi bi-funnel"></i></button>
        <a href="/monitoring/add" class="btn btn-primary">Add</a>
    </div>
</div>
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
@endsection
