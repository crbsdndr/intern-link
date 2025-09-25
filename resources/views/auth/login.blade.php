@extends('layouts.auth')

@section('title', 'Login')
@section('subtitle', 'Access your Internish workspace to guide internships with confidence.')

@section('content')
@if (session('status'))
    <div class="alert alert-info">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('login') }}" class="auth-form">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label bg-amber-950">Email</label>
        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" class="form-control" required>
    </div>
    <div class="text-end mb-3">
        <a class="link-secondary" href="{{ route('signup') }}">New to Internish? Create an account</a>
    </div>
    <button type="submit" class="btn btn-primary w-100">Login</button>
</form>
@endsection
