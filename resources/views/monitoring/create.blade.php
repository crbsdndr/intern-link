@extends('layouts.app')

@section('title', 'Create Monitoring')

@section('content')
<h1 class="mb-4">Create Monitoring</h1>
@include('monitoring.form', [
    'action' => url('/monitorings'),
    'method' => 'POST',
    'log' => null,
    'internships' => $internships,
    'types' => $types,
])
@endsection
