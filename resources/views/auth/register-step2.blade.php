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
        <label><input type="radio" name="role" value="student" {{ old('role', $data['role'] ?? '') === 'student' ? 'checked' : '' }}> Student</label>
        <label><input type="radio" name="role" value="supervisor" {{ old('role', $data['role'] ?? '') === 'supervisor' ? 'checked' : '' }}> Supervisor</label>
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
<a href="{{ route('register') }}">Back</a>
<script>
function toggleFields(role) {
    document.getElementById('student-fields').style.display = role === 'student' ? 'block' : 'none';
    document.getElementById('supervisor-fields').style.display = role === 'supervisor' ? 'block' : 'none';
}
var roleInputs = document.querySelectorAll('input[name="role"]');
roleInputs.forEach(function(r){
    r.addEventListener('change', function(){ toggleFields(this.value); });
    if (r.checked) { toggleFields(r.value); }
});
</script>
</body>
</html>
