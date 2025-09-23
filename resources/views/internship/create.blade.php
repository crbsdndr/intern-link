@extends('layouts.app')

@section('title', 'Create Internship')

@section('content')
<h1>Create Internship</h1>
@include('internship.form', [
    'action' => url('/internship'),
    'method' => 'POST',
    'internship' => null,
    'applications' => $applications,
    'statuses' => $statuses,
    'selected' => [],
    'lockedFirst' => false,
])
@endsection
