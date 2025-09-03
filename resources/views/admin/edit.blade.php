@extends('layouts.app')

@section('title', 'Edit Admin')

@section('content')
<h1>Edit Admin</h1>
@include('admin.form', ['action' => '/admin/' . $admin->id, 'method' => 'PUT', 'admin' => $admin])
@endsection
