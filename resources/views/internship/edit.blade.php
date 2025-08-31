@extends('layouts.app')

@section('title', 'Edit Internship')

@section('content')
<h1>Edit Internship</h1>
@include('internship.form', ['action' => '/internship/' . $internship->id, 'method' => 'PUT', 'internship' => $internship, 'applications' => $applications, 'students' => $students, 'institutions' => $institutions, 'periods' => $periods, 'statuses' => $statuses, 'applicationReadonly' => true])
@endsection
