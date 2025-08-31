@extends('layouts.app')

@section('title', 'Application Details')

@section('content')
<h1>Application Details</h1>
<ul class="list-unstyled">
    <li>Submitted At: {{ $application->submitted_at }}</li>
    <li>Student Name: <a href="/student/{{ $application->student_id }}/see">{{ $application->student_name }}</a></li>
    <li>Institution Name: <a href="/institution/{{ $application->institution_id }}/see">{{ $application->institution_name }}</a></li>
    <li>Status: {{ $application->status }}</li>
    <li>Decision At: {{ $application->decision_at }}</li>
    <li>Rejection Reason: {{ $application->rejection_reason }}</li>
    <li>Notes: {{ $application->notes }}</li>
</ul>
<a href="/application" class="btn btn-secondary mt-3">Back</a>
@endsection
