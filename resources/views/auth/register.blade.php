<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
@if ($errors->any())
    <div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form method="POST" action="{{ route('register.handle') }}">
    @csrf
    <div>
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name') }}" required>
    </div>
    <div>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
    </div>
    <div>
        <label>Password</label>
        <input type="password" name="password" required>
    </div>
    <div>
        <label>Phone</label>
        <input type="number" name="phone" value="{{ old('phone') }}">
    </div>
    <div>
        <label>Role</label>
        <select name="role" required>
            <option value="">Select role</option>
            @foreach (['student','supervisor','admin','developer'] as $role)
                <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit">Sign Up</button>
</form>
</body>
</html>
