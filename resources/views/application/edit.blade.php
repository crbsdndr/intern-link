@extends('layouts.app')

@section('title', 'Edit Application')

@section('content')
<h1>Edit Application</h1>
@include('application.form', ['action' => '/application/' . $application->id, 'method' => 'PUT', 'application' => $application, 'mode' => 'edit'])
@endsection
