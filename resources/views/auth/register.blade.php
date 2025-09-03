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
@if ($step === 1)
<form method="POST" action="{{ route('signup') }}">
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
        <input type="number" name="phone" value="{{ old('phone', $data['phone'] ?? '') }}" required>
    </div>
    <div>
        <label>Role</label>
        <select name="role" required>
            <option value="">Select role</option>
            @foreach (['student','supervisor'] as $role)
                <option value="{{ $role }}" {{ (old('role', $data['role'] ?? '') === $role) ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit">Next</button>
</form>
@else
<form method="POST" action="{{ route('signup') }}" enctype="multipart/form-data">
    @csrf
    @if (($data['role'] ?? '') === 'student')
    <div>
        <label>Student Number</label>
        <input type="number" name="student_number" value="{{ old('student_number', $extra['student_number'] ?? '') }}" required>
    </div>
    <div>
        <label>National Student Number</label>
        <input type="number" name="national_sn" value="{{ old('national_sn', $extra['national_sn'] ?? '') }}" required>
    </div>
    <div>
        <label>Major</label>
        <input type="text" name="major" value="{{ old('major', $extra['major'] ?? '') }}" required>
    </div>
    <div>
        <label>Batch</label>
        <input type="number" name="batch" value="{{ old('batch', $extra['batch'] ?? '') }}" required>
    </div>
    <div>
        <label>Photo (link)</label>
        <input type="text" name="photo" value="{{ old('photo', $extra['photo'] ?? '') }}">
    </div>
    @elseif (($data['role'] ?? '') === 'supervisor')
    <div>
        <label>Supervisor Number</label>
        <input type="text" name="supervisor_number" value="{{ old('supervisor_number', $extra['supervisor_number'] ?? '') }}" required>
    </div>
    <div>
        <label>Department</label>
        <select name="department" required>
            <option value="">Select department</option>
            @foreach ($departments as $dept)
                <option value="{{ $dept }}" {{ (old('department', $extra['department'] ?? '') === $dept) ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Photo (link)</label>
        <input type="text" name="photo" value="{{ old('photo', $extra['photo'] ?? '') }}" required>
    </div>
    @endif
    <button type="submit" name="back" value="1">Back</button>
    <button type="submit">Sign Up</button>
</form>
@endif
</body>
</html>
