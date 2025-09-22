@extends('layouts.app')

@section('title', 'Application Details')

@section('content')
<h1>Application Details</h1>
<div class="card">
    <div class="card-body">
        <ul class="list-unstyled mb-0">
            <li class="mb-2"><strong>Submitted At:</strong> {{ $application->submitted_at ?? '—' }}</li>
            <li class="mb-2"><strong>Student:</strong> <a href="/students/{{ $application->student_id }}/read">{{ $application->student_name }}</a></li>
            <li class="mb-2"><strong>Institution:</strong> <a href="/institutions/{{ $application->institution_id }}/read">{{ $application->institution_name }}</a></li>
            <li class="mb-2"><strong>Status:</strong> {{ $application->status }}</li>
            <li class="mb-2"><strong>Decision At:</strong> {{ $application->decision_at ?? '—' }}</li>
            <li class="mb-2"><strong>Rejection Reason:</strong> {{ $application->rejection_reason ?? '—' }}</li>
            <li><strong>Notes:</strong> {{ $application->notes ?? '—' }}</li>
        </ul>
    </div>
</div>
<a href="/application" class="btn btn-secondary mt-3">Back</a>
@endsection
