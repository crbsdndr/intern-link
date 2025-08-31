@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')
<h1>Edit Student</h1>
@include('student.form', ['action' => '/student/' . $student->id, 'method' => 'PUT', 'student' => $student])
@endsection
