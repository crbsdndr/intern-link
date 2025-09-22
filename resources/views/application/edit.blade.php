@extends('layouts.app')

@section('title', 'Update Application')

@section('content')
<h1>Update Application</h1>
@include('application.form', ['action' => url('/application/' . $application->id), 'method' => 'PUT', 'application' => $application, 'mode' => 'edit'])
@endsection
