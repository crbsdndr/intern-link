<!DOCTYPE html>
<html>
<head>
    <title>Register - Step 2</title>
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
<form method="POST" action="{{ route('register.step2.post') }}" enctype="multipart/form-data">
    @csrf
    <div>
        <label>Role</label>
        <select name="role" id="role" required>
            <option value="">Select role</option>
            <option value="student" {{ old('role', $data['role'] ?? '') === 'student' ? 'selected' : '' }}>Student</option>
            <option value="supervisor" {{ old('role', $data['role'] ?? '') === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
        </select>
    </div>
    <div id="student-fields" style="display:none;">
        <div>
            <label>National Number</label>
            <input type="text" name="national_number" value="{{ old('national_number', $data['national_number'] ?? '') }}">
        </div>
        <div>
            <label>National Student Number</label>
            <input type="text" name="national_student_number" value="{{ old('national_student_number', $data['national_student_number'] ?? '') }}">
        </div>
        <div>
            <label>Major</label>
            <input type="text" name="major" value="{{ old('major', $data['major'] ?? '') }}">
        </div>
        <div>
            <label>Batch</label>
            <input type="number" name="batch" value="{{ old('batch', $data['batch'] ?? '') }}">
        </div>
        <div>
            <label>Photo</label>
            <input type="file" name="photo">
        </div>
    </div>
    <div id="supervisor-fields" style="display:none;">
        <div>
            <label>Supervisor Number</label>
            <input type="text" name="supervisor_number" value="{{ old('supervisor_number', $data['supervisor_number'] ?? '') }}">
        </div>
        <div>
            <label>Department</label>
            <input type="text" name="department" value="{{ old('department', $data['department'] ?? '') }}">
        </div>
        <div>
            <label>Photo</label>
            <input type="file" name="photo">
        </div>
    </div>
    <button type="submit">Continue</button>
</form>
<script>
function toggleFields() {
    var role = document.getElementById('role').value;
    var student = document.getElementById('student-fields');
    var supervisor = document.getElementById('supervisor-fields');
    student.style.display = role === 'student' ? 'block' : 'none';
    supervisor.style.display = role === 'supervisor' ? 'block' : 'none';
    student.querySelectorAll('input').forEach(function(el){ el.disabled = role !== 'student'; });
    supervisor.querySelectorAll('input').forEach(function(el){ el.disabled = role !== 'supervisor'; });
}

document.getElementById('role').addEventListener('change', toggleFields);
toggleFields();
</script>
</body>
</html>
