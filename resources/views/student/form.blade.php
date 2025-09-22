<form action="{{ $action }}" method="POST" class="row g-3">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')

    <div class="col-12">
        <label class="form-label" for="student-name">Name</label>
        <input type="text" name="name" id="student-name" class="form-control" value="{{ old('name', optional($student)->name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="student-email">Email</label>
        <input type="email" name="email" id="student-email" class="form-control" value="{{ old('email', optional($student)->email) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="student-phone">Phone</label>
        <input type="text" name="phone" id="student-phone" class="form-control" value="{{ old('phone', optional($student)->phone) }}" inputmode="numeric">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="student-password">Password</label>
        <input type="password" name="password" id="student-password" class="form-control" @if($method === 'POST') required @endif>
        @if($method === 'PUT')
            <div class="form-text">Leave blank to keep the current password.</div>
        @endif
    </div>
    <div class="col-md-6">
        <label class="form-label" for="student-number">Student Number</label>
        <input type="text" name="student_number" id="student-number" class="form-control" value="{{ old('student_number', optional($student)->student_number) }}" inputmode="numeric" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="student-national-sn">National Student Number</label>
        <input type="text" name="national_sn" id="student-national-sn" class="form-control" value="{{ old('national_sn', optional($student)->national_sn) }}" inputmode="numeric" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="student-major">Major</label>
        <input type="text" name="major" id="student-major" class="form-control" value="{{ old('major', optional($student)->major) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="student-class">Class</label>
        <input type="text" name="class" id="student-class" class="form-control" value="{{ old('class', optional($student)->class) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="student-batch">Batch</label>
        <input type="number" name="batch" id="student-batch" class="form-control" value="{{ old('batch', optional($student)->batch) }}" min="1900" max="2100" step="1" required>
    </div>
    <div class="col-12">
        <label class="form-label" for="student-notes">Notes</label>
        <textarea name="notes" id="student-notes" class="form-control" rows="4">{{ old('notes', optional($student)->notes) }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label" for="student-photo">Photo</label>
        <input type="text" name="photo" id="student-photo" class="form-control" value="{{ old('photo', optional($student)->photo) }}">
    </div>
    <div class="col-12 d-flex gap-2">
        <a href="/students" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>
