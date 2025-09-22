@extends('layouts.app')

@section('title', 'Update Admin')

@section('content')
<h1>Update Admin</h1>
@include('admin.form', ['action' => url('/admins/' . $admin->id), 'method' => 'PUT', 'admin' => $admin])
@endsection
