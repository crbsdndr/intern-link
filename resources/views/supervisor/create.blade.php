@extends('layouts.app')

@section('title', 'Create Supervisor')

@section('content')
<h1>Create Supervisor</h1>
@include('supervisor.form', ['action' => url('/supervisors'), 'method' => 'POST', 'supervisor' => null])
@endsection
