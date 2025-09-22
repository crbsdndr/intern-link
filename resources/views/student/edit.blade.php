@extends('layouts.app')

@section('title', 'Update Student')

@section('content')
<h1>Update Student</h1>
@include('student.form', ['action' => url('/students/' . $student->id), 'method' => 'PUT', 'student' => $student])
@endsection
