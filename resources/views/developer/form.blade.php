@php($isUpdate = $method === 'PUT')
<form action="{{ $action }}" method="POST">
    @csrf
    @if($isUpdate)
        @method('PUT')
    @endif

    @include('components.form-errors')

    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', optional($developer)->name) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', optional($developer)->email) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="number" name="phone" class="form-control" value="{{ old('phone', optional($developer)->phone) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" @if(!$isUpdate) required @endif>
        @if($isUpdate)
            <div class="form-text">Leave blank to keep the current password.</div>
        @endif
    </div>
    <a href="/developers" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
