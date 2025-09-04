<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')
    <div class="mb-3">
        <label class="form-label">Institution Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', optional($institution)->name) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control">{{ old('address', optional($institution)->address) }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">City</label>
        <select name="city" class="form-control">
            <option value="">-- Select City --</option>
            @foreach($cities as $city)
                <option value="{{ $city }}" {{ old('city', optional($institution)->city) == $city ? 'selected' : '' }}>{{ $city }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Province</label>
        <select name="province" class="form-control">
            <option value="">-- Select Province --</option>
            @foreach($provinces as $province)
                <option value="{{ $province }}" {{ old('province', optional($institution)->province) == $province ? 'selected' : '' }}>{{ $province }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Industry</label>
        <input type="text" name="industry" class="form-control" value="{{ old('industry', optional($institution)->industry) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Website</label>
        <textarea name="website" class="form-control">{{ old('website', optional($institution)->website) }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Photo</label>
        <input type="text" name="photo" class="form-control" value="{{ old('photo', optional($institution)->photo) }}">
    </div>
    <hr>
    <div class="mb-3">
        <label class="form-label">Contact Name</label>
        <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name', optional($institution)->contact_name) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Contact Email</label>
        <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', optional($institution)->contact_email) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Contact Phone</label>
        <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', optional($institution)->contact_phone) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Contact Position</label>
        <input type="text" name="contact_position" class="form-control" value="{{ old('contact_position', optional($institution)->contact_position) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Contact Primary</label>
        <select name="contact_primary" class="form-control">
            <option value="0" {{ old('contact_primary', optional($institution)->contact_primary) ? '' : 'selected' }}>False</option>
            <option value="1" {{ old('contact_primary', optional($institution)->contact_primary) ? 'selected' : '' }}>True</option>
        </select>
    </div>
    <hr>
    <div class="mb-3">
        <label class="form-label">Period Year</label>
        <input type="number" name="period_year" class="form-control" value="{{ old('period_year', optional($institution)->period_year) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Period Term</label>
        <input type="number" name="period_term" class="form-control" value="{{ old('period_term', optional($institution)->period_term) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Quota</label>
        <input type="number" name="quota" class="form-control" value="{{ old('quota', optional($institution)->quota) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Used</label>
        <input type="number" name="used" class="form-control" value="{{ old('used', optional($institution)->used) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control">{{ old('notes', optional($institution)->notes) }}</textarea>
    </div>
    <a href="/institution" class="btn btn-secondary">Back</a>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
