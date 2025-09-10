<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')

    @php $mode = $mode ?? null; @endphp

    @if($mode === 'create')
    <div id="students-wrapper">
        <div class="mb-3 student-item d-flex align-items-start">
            <div class="flex-grow-1">
                <label class="form-label">Student Name</label>
                <select name="student_ids[]" class="form-select tom-select">
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <button type="button" id="add-student" class="btn btn-secondary mb-3">+</button>
    <template id="student-template">
        <div class="mb-3 student-item d-flex align-items-start">
            <div class="flex-grow-1">
                <label class="form-label">Student Name</label>
                <select name="student_ids[]" class="form-select tom-select">
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn btn-danger ms-2 remove-student">-</button>
        </div>
    </template>
    @elseif($mode === 'edit')
    <div id="students-wrapper">
        <div class="mb-3 student-item">
            <label class="form-label">Student Name</label>
            <input type="hidden" name="student_ids[]" value="{{ $application->student_id }}">
            <input type="text" class="form-control" value="{{ $application->student_name }}" readonly>
        </div>
    </div>
    <button type="button" id="add-student" class="btn btn-secondary mb-3">+</button>
    <template id="student-template">
        <div class="mb-3 student-item d-flex align-items-start">
            <div class="flex-grow-1">
                <label class="form-label">Student Name</label>
                <select name="student_ids[]" class="form-select tom-select">
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn btn-danger ms-2 remove-student">-</button>
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

    <a href="/application" class="btn btn-secondary">Back</a>
    <button type="submit" class="btn btn-primary">Save</button>
</form>

@if($mode === 'create' || $mode === 'edit')
<script>
const wrapper = document.getElementById('students-wrapper');
document.getElementById('add-student').addEventListener('click', function(){
    const tpl = document.getElementById('student-template');
    const clone = tpl.content.cloneNode(true);
    wrapper.appendChild(clone);
    window.initTomSelect();
});
wrapper.addEventListener('click', function(e){
    if(e.target.classList.contains('remove-student')){
        e.target.closest('.student-item').remove();
    }
});
</script>
@endif

