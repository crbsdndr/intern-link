@extends('layouts.app')

@section('title', 'Update Monitoring Log')

@section('content')
<h1>Update Monitoring Log</h1>
@include('monitoring.form', [
    'action' => url('/monitoring/' . $log->monitoring_log_id),
    'method' => 'PUT',
    'log' => $log,
    'internships' => $internships,
    'supervisors' => $supervisors,
    'types' => $types,
    'readonly' => true,
])
@endsection
