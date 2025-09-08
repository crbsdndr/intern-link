<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')

    @if($multi ?? false)
    <div id="students-wrapper">
        <div class="mb-3 student-item">
            <label class="form-label">Student Name</label>
            <select name="student_ids[]" class="form-select tom-select">
                @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <button type="button" id="add-student" class="btn btn-secondary mb-3">+</button>
    <template id="student-template">
        <div class="mb-3 student-item">
            <label class="form-label">Student Name</label>
            <select name="student_ids[]" class="form-select tom-select">
                @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                @endforeach
            </select>
        </div>
    </template>
    @else
    <div class="mb-3">
        <label class="form-label">Student Name</label>
        <select name="student_id" class="form-select tom-select">
            @foreach($students as $student)
                <option value="{{ $student->id }}" {{ old('student_id', optional($application)->student_id) == $student->id ? 'selected' : '' }}>{{ $student->name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="mb-3">
        <label class="form-label">Institution Name</label>
        <select name="institution_id" class="form-select tom-select">
            @foreach($institutions as $institution)
                <option value="{{ $institution->id }}" {{ old('institution_id', optional($application)->institution_id) == $institution->id ? 'selected' : '' }}>{{ $institution->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select tom-select">
            @foreach($statuses as $status)
                <option value="{{ $status }}" {{ old('status', optional($application)->status) == $status ? 'selected' : '' }}>{{ $status }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Submitted At</label>
        <input type="date" name="submitted_at" class="form-control" value="{{ old('submitted_at', optional($application)->submitted_at ? \Illuminate\Support\Carbon::parse($application->submitted_at)->format('Y-m-d') : '') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Decision At</label>
        <input type="date" name="decision_at" class="form-control" value="{{ old('decision_at', optional($application)->decision_at ? \Illuminate\Support\Carbon::parse($application->decision_at)->format('Y-m-d') : '') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Rejection Reason</label>
        <textarea name="rejection_reason" class="form-control">{{ old('rejection_reason', optional($application)->rejection_reason) }}</textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control">{{ old('notes', optional($application)->notes) }}</textarea>
    </div>

    @if($applyAll ?? false)
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="apply_to_all" name="apply_to_all" value="1" {{ old('apply_to_all') ? 'checked' : '' }}>
        <label class="form-check-label" for="apply_to_all">Terapkan perubahan ke semua aplikasi untuk institusi ini</label>
    </div>
    @endif

    <a href="/application" class="btn btn-secondary">Back</a>
    <button type="submit" class="btn btn-primary">Save</button>
</form>

@if($multi ?? false)
<script>
document.getElementById('add-student').addEventListener('click', function(){
    const tpl = document.getElementById('student-template');
    const clone = tpl.content.cloneNode(true);
    document.getElementById('students-wrapper').appendChild(clone);
    window.initTomSelect();
});
</script>
@endif
