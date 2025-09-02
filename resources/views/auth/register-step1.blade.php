<!DOCTYPE html>
<html>
<head>
    <title>Register - Step 1</title>
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
<form method="POST" action="{{ route('register.post') }}">
    @csrf
    <div>
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name', $data['name'] ?? '') }}" required>
    </div>
    <div>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $data['email'] ?? '') }}" required>
    </div>
    <div>
        <label>Password</label>
        <input type="password" name="password" required>
    </div>
    <div>
        <label>Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $data['phone'] ?? '') }}">
    </div>
    <button type="submit">Next</button>
</form>
</body>
</html>
