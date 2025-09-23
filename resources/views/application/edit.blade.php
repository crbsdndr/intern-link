@extends('layouts.app')

@section('title', 'Update Application')

@section('content')
<h1 class="mb-4">Update Application</h1>

<form action="/applications/{{ $application->id }}" method="POST" class="card">
    @csrf
    @method('PUT')
    <div class="card-body d-flex flex-column gap-3">
        @include('components.form-errors')

        <div id="student-section" data-base-id="{{ $application->student_id }}" data-is-student="{{ $isStudent ? '1' : '0' }}">
            <input type="hidden" name="student_ids[]" value="{{ $application->student_id }}">
            <label class="form-label">Student Name</label>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <select class="form-select tom-select" disabled>
                    <option selected>{{ $application->student_name }}</option>
                </select>
                @unless($isStudent)
                    <button type="button" class="btn btn-outline-secondary" id="add-student-btn">+</button>
                @endunless
            </div>
            <div id="additional-students" class="mt-3 d-flex flex-column gap-2">
                @php($oldStudents = collect(old('student_ids', [$application->student_id]))->map(fn($id) => (int) $id)->filter(fn($id) => $id !== (int) $application->student_id))
                @foreach($oldStudents as $studentId)
                    <div class="d-flex gap-2 align-items-center additional-student">
                        <select name="student_ids[]" class="form-select tom-select student-select" data-selected="{{ $studentId }}"></select>
                        <button type="button" class="btn btn-outline-danger remove-student">-</button>
                    </div>
                @endforeach
            </div>
            @unless($isStudent)
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="apply-all" name="apply_all" value="1" @checked(old('apply_all'))>
                    <label class="form-check-label" for="apply-all">Apply to all applications with the same institution</label>
                </div>
            @endunless
        </div>

        <div>
            <label for="institution-id" class="form-label">Institution Name</label>
            <select name="institution_id" id="institution-id" class="form-select tom-select">
                @foreach($institutions as $institution)
                    <option value="{{ $institution->id }}" @selected((string)old('institution_id', $application->institution_id) === (string)$institution->id)>{{ $institution->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="period-id" class="form-label">Period</label>
            <select name="period_id" id="period-id" class="form-select tom-select" data-tom-allow-empty="true">
                @foreach($periods as $period)
                    <option value="{{ $period['id'] }}" @selected((string)old('period_id', $application->period_id) === (string)$period['id'])>{{ $period['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" class="form-label">Status Application</label>
            <select name="status" id="status" class="form-select">
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $application->status) === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>

        @if($canSetStudentAccess)
            <div>
                <label class="form-label d-block">Student Access</label>
                @php($studentAccess = old('student_access', $application->student_access ? 'true' : 'false'))
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="student_access" id="student-access-true-update" value="true" @checked($studentAccess === 'true')>
                    <label class="form-check-label" for="student-access-true-update">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="student_access" id="student-access-false-update" value="false" @checked($studentAccess === 'false')>
                    <label class="form-check-label" for="student-access-false-update">False</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="student_access" id="student-access-any-update" value="any" @checked($studentAccess === 'any')>
                    <label class="form-check-label" for="student-access-any-update">Any</label>
                </div>
            </div>
        @endif

        <div>
            <label for="submitted-at" class="form-label">Submitted At</label>
            <input type="date" name="submitted_at" id="submitted-at" class="form-control" value="{{ old('submitted_at', \Illuminate\Support\Carbon::parse($application->submitted_at)->format('Y-m-d')) }}">
        </div>

        <div>
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" id="notes" class="form-control" rows="4">{{ old('notes', $application->application_notes) }}</textarea>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="/applications/{{ $application->id }}/read" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

@push('scripts')
<script>
(() => {
    const studentSection = document.getElementById('student-section');
    const baseStudentId = parseInt(studentSection.dataset.baseId, 10);
    const isStudent = studentSection.dataset.isStudent === '1';
    const allStudents = @json($allStudentsForInstitution->map(fn($student) => [
        'id' => $student->student_id,
        'name' => $student->student_name,
    ]));
    const addBtn = document.getElementById('add-student-btn');
    const container = document.getElementById('additional-students');
    const applyAll = document.getElementById('apply-all');

    function buildOption(select, student, selectedIds) {
        if (selectedIds.includes(String(student.id)) && String(student.id) !== select.dataset.current) {
            return;
        }
        const option = document.createElement('option');
        option.value = student.id;
        option.textContent = student.name;
        if (String(student.id) === select.dataset.current) {
            option.selected = true;
        }
        select.appendChild(option);
    }

    function populateSelect(select) {
        if (select.tomselect) {
            select.tomselect.destroy();
        }
        const selects = container.querySelectorAll('select.student-select');
        const selected = Array.from(selects).map(s => s.value).concat(String(baseStudentId));
        select.innerHTML = '';
        allStudents.forEach(student => buildOption(select, student, selected.filter(id => id !== select.dataset.current)));
        if (!select.value && select.options.length) {
            select.value = select.options[0].value;
        }
        select.dataset.current = select.value;
        window.initTomSelect && window.initTomSelect();
    }

    function populateAllSelects() {
        container.querySelectorAll('select.student-select').forEach(populateSelect);
        updateAddButtonState();
    }

    function createSelect(selectedId = null) {
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex gap-2 align-items-center additional-student';
        const select = document.createElement('select');
        select.name = 'student_ids[]';
        select.className = 'form-select tom-select student-select';
        select.dataset.current = selectedId ? String(selectedId) : '';
        if (selectedId) {
            select.dataset.selected = String(selectedId);
        }
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-outline-danger remove-student';
        removeBtn.textContent = '-';
        wrapper.appendChild(select);
        wrapper.appendChild(removeBtn);
        container.appendChild(wrapper);
        populateSelect(select);
    }

    function updateAddButtonState() {
        if (!addBtn) return;
        const selects = container.querySelectorAll('select.student-select');
        const selected = Array.from(selects).map(s => s.value).concat(String(baseStudentId));
        const remaining = allStudents.filter(student => !selected.includes(String(student.id)));
        addBtn.disabled = remaining.length === 0;
    }

    container.addEventListener('click', (event) => {
        if (!event.target.classList.contains('remove-student')) {
            return;
        }
        event.target.closest('.additional-student').remove();
        populateAllSelects();
    });

    container.addEventListener('change', (event) => {
        const target = event.target;
        if (!target.classList.contains('student-select')) {
            return;
        }
        target.dataset.current = target.value;
        populateAllSelects();
    });

    if (addBtn) {
        addBtn.addEventListener('click', () => {
            createSelect();
            populateAllSelects();
        });
    }

    if (applyAll) {
        applyAll.addEventListener('change', () => {
            if (!applyAll.checked) {
                updateAddButtonState();
                return;
            }
            const selects = container.querySelectorAll('select.student-select');
            const selected = Array.from(selects).map(s => s.value).concat(String(baseStudentId));
            allStudents.forEach(student => {
                if (!selected.includes(String(student.id))) {
                    createSelect(student.id);
                }
            });
            populateAllSelects();
        });
    }

    if (!container.children.length && !isStudent && addBtn) {
        addBtn.disabled = allStudents.filter(student => student.id !== baseStudentId).length === 0;
    }

    container.querySelectorAll('select.student-select').forEach((select) => {
        const preset = select.dataset.selected;
        if (preset) {
            select.dataset.current = preset;
        }
    });

    populateAllSelects();
})();
</script>
@endpush
@endsection
