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

@if (isset($step1))
    <form method="POST" action="{{ route('register.handle') }}" enctype="multipart/form-data">
        @csrf
        @if ($step1['role'] === 'student')
            <div>
                <label>National Number</label>
                <input type="text" name="national_number" value="{{ old('national_number') }}" required>
            </div>
            <div>
                <label>National Student Number</label>
                <input type="text" name="national_student_number" value="{{ old('national_student_number') }}" required>
            </div>
            <div>
                <label>Major</label>
                <input type="text" name="major" value="{{ old('major') }}" required>
            </div>
            <div>
                <label>Batch</label>
                <input type="number" name="batch" value="{{ old('batch') }}" required>
            </div>
            <div>
                <label>Photo</label>
                <input type="file" name="photo" required>
            </div>
        @elseif ($step1['role'] === 'supervisor')
            <div>
                <label>Supervisor Number</label>
                <input type="text" name="supervisor_number" value="{{ old('supervisor_number') }}" required>
            </div>
            <div>
                <label>Department</label>
                <input type="text" name="department" value="{{ old('department') }}" required>
            </div>
            <div>
                <label>Photo</label>
                <input type="file" name="photo" required>
            </div>
        @endif
        <button type="submit">Continue</button>
    </form>
    <form method="POST" action="{{ route('register.handle') }}">
        @csrf
        <input type="hidden" name="cancel" value="1">
        <button type="submit">Back</button>
    </form>
@else
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
@endif
</body>
</html>
