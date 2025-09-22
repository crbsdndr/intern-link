<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')

    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', optional($supervisor)->name) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', optional($supervisor)->email) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="number" name="phone" class="form-control" value="{{ old('phone', optional($supervisor)->phone) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" @if($method !== 'PUT') required @endif>
    </div>
    <div class="mb-3">
        <label class="form-label">Supervisor Number</label>
        <input type="number" name="supervisor_number" class="form-control" value="{{ old('supervisor_number', optional($supervisor)->supervisor_number) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Department</label>
        <input type="text" name="department" class="form-control" value="{{ old('department', optional($supervisor)->department) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="4">{{ old('notes', optional($supervisor)->notes) }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Photo</label>
        <input type="text" name="photo" class="form-control" value="{{ old('photo', optional($supervisor)->photo) }}">
    </div>
    <a href="/supervisors" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
