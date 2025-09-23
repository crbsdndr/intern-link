@extends('layouts.app')

@section('title', 'Internship Details')

@section('content')
<h1>Internship Details</h1>
<div class="card">
    <div class="card-body">
        <ul class="list-unstyled mb-0">
            <li class="mb-2"><strong>Student:</strong> <a href="/applications/{{ $internship->application_id }}/read">{{ $internship->student_name }}</a></li>
            <li class="mb-2"><strong>Institution:</strong> {{ $internship->institution_name }}</li>
            <li class="mb-2"><strong>Start Date:</strong> {{ $internship->start_date ?? '—' }}</li>
            <li class="mb-2"><strong>End Date:</strong> {{ $internship->end_date ?? '—' }}</li>
            <li><strong>Status:</strong> {{ $internship->status }}</li>
        </ul>
    </div>
</div>
<a href="/internship" class="btn btn-secondary mt-3">Back</a>
@endsection
