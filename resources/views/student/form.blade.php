<form action="{{ $action }}" method="POST" novalidate>
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', optional($student)->name) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', optional($student)->email) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" inputmode="numeric" pattern="[0-9]*" class="form-control" value="{{ old('phone', optional($student)->phone) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Student Number</label>
        <input type="text" name="student_number" inputmode="numeric" class="form-control" value="{{ old('student_number', optional($student)->student_number) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">National Student Number</label>
        <input type="text" name="national_sn" inputmode="numeric" class="form-control" value="{{ old('national_sn', optional($student)->national_sn) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Major</label>
        <input type="text" name="major" class="form-control" value="{{ old('major', optional($student)->major) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Class</label>
        <input type="text" name="class" class="form-control" value="{{ old('class', optional($student)->class) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Batch</label>
        <input type="number" name="batch" min="1900" max="2100" step="1" class="form-control" value="{{ old('batch', optional($student)->batch) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control">{{ old('notes', optional($student)->notes) }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Photo</label>
        <input type="text" name="photo" class="form-control" value="{{ old('photo', optional($student)->photo) }}">
    </div>
    <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
