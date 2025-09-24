@extends('layouts.app')

@section('title', 'Add Monitoring Log')

@section('content')
<h1>Add Monitoring Log</h1>
@include('monitoring.form', [
    'action' => '/monitoring',
    'method' => 'POST',
    'log' => null,
    'internships' => $internships,
    'supervisors' => $supervisors,
    'types' => $types,
    'readonly' => false,
])
@endsection
