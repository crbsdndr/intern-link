@extends('layouts.app')

@section('title', 'Create Admin')

@section('content')
<h1>Create Admin</h1>
@include('admin.form', ['action' => url('/admins'), 'method' => 'POST', 'admin' => null])
@endsection
