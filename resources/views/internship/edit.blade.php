@extends('layouts.app')

@section('title', 'Update Internship')

@section('content')
<h1>Update Internship</h1>
@include('internship.form', [
    'action' => url('/internship/' . $internship->id),
    'method' => 'PUT',
    'internship' => $internship,
    'applications' => $applications,
    'statuses' => $statuses,
    'selected' => [$internship->application_id],
    'lockedFirst' => true,
])
@endsection
