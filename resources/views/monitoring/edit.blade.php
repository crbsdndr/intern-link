@extends('layouts.app')

@section('title', 'Update Monitorings')

@section('content')
<h1 class="mb-4">Update Monitorings</h1>
@include('monitoring.form', [
    'action' => url('/monitorings/' . $log->monitoring_log_id),
    'method' => 'PUT',
    'log' => $log,
    'internships' => $internships,
    'types' => $types,
])
@endsection
