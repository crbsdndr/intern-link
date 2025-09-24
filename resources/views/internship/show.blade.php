@extends('layouts.app')

@section('title', 'Internship Details')

@section('content')
<h1><a href="/application/{{ $internship->application_id }}/see">{{ $internship->student_name }} – {{ $internship->institution_name }}</a></h1>
<ul class="list-unstyled">
    <li>Start Date: {{ $internship->start_date }}</li>
    <li>End Date: {{ $internship->end_date }}</li>
    <li>Status: {{ $internship->status }}</li>
</ul>
<a href="/internship" class="btn btn-secondary mt-3">Back</a>
@endsection
