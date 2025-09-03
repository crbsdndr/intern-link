<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', optional($admin)->name) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', optional($admin)->email) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', optional($admin)->phone) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Role</label>
        <input type="text" class="form-control" value="{{ old('role', optional($admin)->role) }}" readonly>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control">
    </div>
    <a href="/admin" class="btn btn-secondary">Back</a>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
