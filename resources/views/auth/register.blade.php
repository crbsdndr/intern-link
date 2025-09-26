@extends('layouts.auth')

@section('title', 'Register')
@section('subtitle', 'Set up your InternLink access in two quick steps.')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="auth-step mb-4">
    <span class="badge bg-primary-subtle text-primary fw-semibold">Step {{ $step }} of 2</span>
</div>

@if ($step === 1)
<form method="POST" action="{{ route('signup') }}" class="auth-form">
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $data['name'] ?? '') }}" required autofocus>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $data['email'] ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="phone" class="form-label">Phone</label>
        <input type="number" id="phone" name="phone" class="form-control" value="{{ old('phone', $data['phone'] ?? '') }}" required>
    </div>
    <div class="mb-4">
        <label for="role" class="form-label">Role</label>
        <select id="role" name="role" class="form-select" required>
            <option value="">Select role</option>
            @foreach (['student','supervisor'] as $role)
                <option value="{{ $role }}" {{ (old('role', $data['role'] ?? '') === $role) ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary w-100">Next</button>
    <p class="text-center mt-3 mb-0">Already have an account? <a class="link-secondary" href="{{ route('login') }}">Login</a></p>
</form>
@else
<form method="POST" action="{{ route('signup') }}" enctype="multipart/form-data" class="auth-form">
    @csrf
    @if (($data['role'] ?? '') === 'student')
    <div class="mb-3">
        <label for="student_number" class="form-label">Student Number</label>
        <input type="number" id="student_number" name="student_number" class="form-control" value="{{ old('student_number', $extra['student_number'] ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label for="national_sn" class="form-label">National Student Number</label>
        <input type="number" id="national_sn" name="national_sn" class="form-control" value="{{ old('national_sn', $extra['national_sn'] ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label for="major" class="form-label">Major</label>
        <input type="text" id="major" name="major" class="form-control" value="{{ old('major', $extra['major'] ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label for="batch" class="form-label">Batch</label>
        <input type="number" id="batch" name="batch" class="form-control" value="{{ old('batch', $extra['batch'] ?? '') }}" required>
    </div>
    <div class="mb-4">
        <label for="photo" class="form-label">Photo (link)</label>
        <input type="text" id="photo" name="photo" class="form-control" value="{{ old('photo', $extra['photo'] ?? '') }}">
    </div>
    @elseif (($data['role'] ?? '') === 'supervisor')
    <div class="mb-3">
        <label for="supervisor_number" class="form-label">Supervisor Number</label>
        <input type="text" id="supervisor_number" name="supervisor_number" class="form-control" value="{{ old('supervisor_number', $extra['supervisor_number'] ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label for="department" class="form-label">Department</label>
        <input type="text" id="department" name="department" class="form-control" value="{{ old('department', $extra['department'] ?? '') }}" required>
    </div>
    <div class="mb-4">
        <label for="photo" class="form-label">Photo (link)</label>
        <input type="text" id="photo" name="photo" class="form-control" value="{{ old('photo', $extra['photo'] ?? '') }}" required>
    </div>
    @endif
    <div class="d-flex gap-2">
        <button type="submit" name="back" value="1" formnovalidate class="btn btn-outline-secondary flex-fill">Back</button>
        <button type="submit" class="btn btn-primary flex-fill">Sign Up</button>
    </div>
</form>
@endif
@endsection
