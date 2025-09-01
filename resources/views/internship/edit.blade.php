@extends('layouts.app')

@section('title', 'Edit Internship')

@section('content')
<h1>Edit Internship</h1>
@include('internship.form', [
    'action' => '/internship/' . $internship->id,
    'method' => 'PUT',
    'internship' => $internship,
    'applications' => $applications,
    'statuses' => $statuses,
    'readonly' => true,
])
@endsection
