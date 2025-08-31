@extends('layouts.app')

@section('title', 'Add Supervisor')

@section('content')
<h1>Add Supervisor</h1>
@include('supervisor.form', ['action' => '/supervisor', 'method' => 'POST', 'supervisor' => null])
@endsection
