@extends('layouts.app')

@section('title', 'Edit Monitoring Log')

@section('content')
<h1>Edit Monitoring Log</h1>
@include('monitoring.form', [
    'action' => '/monitoring/' . $log->monitoring_log_id,
    'method' => 'PUT',
    'log' => $log,
    'internships' => $internships,
    'supervisors' => $supervisors,
    'types' => $types,
    'readonly' => true,
])
@endsection
