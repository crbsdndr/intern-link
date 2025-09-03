<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
@if (session('status'))
    <div>{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
    </div>
    <div>
        <label>Password</label>
        <input type="password" name="password" required>
    </div>
    <p>New to Internish? <a href="{{ route('signup') }}">Create an account</a></p>
    <button type="submit">Login</button>
</form>
</body>
</html>
