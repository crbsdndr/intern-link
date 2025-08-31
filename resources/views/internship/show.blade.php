@extends('layouts.app')

@section('title', 'Internship Details')

@section('content')
<h1>{{ $internship->student_name }} - {{ $internship->institution_name }}</h1>
<ul class="list-unstyled">
    <li>Student Name: <a href="/student/{{ $internship->student_id }}/see">{{ $internship->student_name }}</a></li>
    <li>Institution Name: <a href="/institution/{{ $internship->institution_id }}/see">{{ $internship->institution_name }}</a></li>
    <li>Year: {{ $internship->period_year }}</li>
    <li>Semester: {{ $internship->period_term }}</li>
    <li>Start Date: {{ $internship->start_date }}</li>
    <li>End Date: {{ $internship->end_date }}</li>
    <li>Status: {{ $internship->status }}</li>
    <li>Notes: {{ $internship->notes }}</li>
</ul>
<a href="/internship" class="btn btn-secondary mt-3">Back</a>
@endsection
