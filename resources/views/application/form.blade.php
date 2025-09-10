<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')

    @php $mode = $mode ?? null; @endphp

    @if($mode === 'create' || $mode === 'edit')
    <div class="mb-3">
        <label class="form-label">Students</label>
        <div id="students-wrapper">
            @if($mode === 'create')
            <div class="d-flex mb-2 student-item">
                <select name="student_ids[]" class="form-select tom-select">
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-danger ms-2 remove-student d-none">-</button>
            </div>
            @else
            <div class="d-flex mb-2 student-item">
                <input type="hidden" name="student_ids[]" value="{{ $application->student_id }}">
                <input type="text" class="form-control" value="{{ $application->student_name }}" readonly>
            </div>
            @endif
        </div>
        <button type="button" id="add-student" class="btn btn-secondary mt-2">+</button>
        @if($mode === 'edit')
        <div class="form-check mt-2">
            <input type="checkbox" class="form-check-input" id="apply-all" name="apply_all" value="1">
            <label class="form-check-label" for="apply-all">Apply to all students of this institution</label>
        </div>
        @endif
        <template id="student-template">
            <div class="d-flex mb-2 student-item">
                <select name="student_ids[]" class="form-select tom-select">
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-danger ms-2 remove-student">-</button>
            </div>
        </template>
    </div>
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
const addBtn = document.getElementById('add-student');
const allStudents = @json($students->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values());
let applyAll = null;

function updateOptions() {
    if (applyAll && applyAll.checked) {
        if (addBtn) addBtn.disabled = true;
        return;
    }
    const selects = wrapper.querySelectorAll('select[name="student_ids[]"]');
    const selected = Array.from(selects).map(s => s.value);
    selects.forEach((select) => {
        const current = select.value;
        if (select.tomselect) {
            select.tomselect.destroy();
        }
        select.innerHTML = '';
        allStudents.forEach(st => {
            if (!selected.includes(String(st.id)) || String(st.id) === current) {
                const opt = document.createElement('option');
                opt.value = st.id;
                opt.textContent = st.name;
                if (String(st.id) === current) opt.selected = true;
                select.appendChild(opt);
            }
        });
    });
    window.initTomSelect();
    const remaining = allStudents.filter(st => !selected.includes(String(st.id)));
    if (addBtn) {
        addBtn.disabled = remaining.length === 0;
    }
}

if (addBtn) {
    addBtn.addEventListener('click', function(){
        const tpl = document.getElementById('student-template');
        const clone = tpl.content.cloneNode(true);
        const newSelect = clone.querySelector('select[name="student_ids[]"]');
        const currentSelected = Array.from(wrapper.querySelectorAll('select[name="student_ids[]"]')).map(s => s.value);
        const remaining = allStudents.filter(st => !currentSelected.includes(String(st.id)));
        if (remaining.length === 0) return;
        newSelect.value = remaining[0].id;
        wrapper.appendChild(clone);
        updateOptions();
    });
}

wrapper.addEventListener('click', function(e){
    if(e.target.classList.contains('remove-student')){
        e.target.closest('.student-item').remove();
        updateOptions();
    }
});

wrapper.addEventListener('change', function(e){
    if(e.target.matches('select[name="student_ids[]"]')){
        updateOptions();
    }
});

updateOptions();

@if($mode === 'edit')
applyAll = document.getElementById('apply-all');
if (applyAll) {
    applyAll.addEventListener('change', function(){
        if (this.checked) {
            wrapper.querySelectorAll('.student-item').forEach((el, idx) => { if (idx > 0) el.remove(); });
            wrapper.querySelectorAll('.remove-student').forEach(btn => btn.disabled = true);
            if (addBtn) addBtn.disabled = true;
        } else {
            wrapper.querySelectorAll('.remove-student').forEach(btn => btn.disabled = false);
            updateOptions();
        }
    });
}
@endif
</script>
@endif

