@extends('layouts.app')

@section('title', 'Update Student')

@section('content')
<h1>Update Student</h1>
@include('student.form', ['action' => '/students/' . $student->id, 'method' => 'PUT', 'student' => $student])
@endsection
