@extends('layouts.app')

@section('title', 'Edit Supervisor')

@section('content')
<h1>Edit Supervisor</h1>
@include('supervisor.form', ['action' => '/supervisor/' . $supervisor->id, 'method' => 'PUT', 'supervisor' => $supervisor])
@endsection
