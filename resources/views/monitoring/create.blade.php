@extends('layouts.app')

@section('title', 'Create Monitoring Log')

@section('content')
<h1>Create Monitoring Log</h1>
@include('monitoring.form', [
    'action' => url('/monitoring'),
    'method' => 'POST',
    'log' => null,
    'internships' => $internships,
    'supervisors' => $supervisors,
    'types' => $types,
    'readonly' => false,
])
@endsection
