@extends('layouts.app')

@section('title', 'Create Application')

@section('content')
<h1 class="mb-4">Create Application</h1>

<form action="/applications" method="POST" class="card">
    @csrf
    <div class="card-body d-flex flex-column gap-3">
        @include('components.form-errors')

        @php
            $defaultStudentIds = collect(old('student_ids'));
            if ($defaultStudentIds->isEmpty() && $students->count() === 1) {
                $defaultStudentIds = collect([(int) $students->first()->id]);
            }
            $defaultStudentIds = $defaultStudentIds
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->map(fn ($value) => (int) $value)
                ->values();
            $baseStudentId = $defaultStudentIds->first();
        @endphp

        <div id="student-section" data-is-student="{{ $isStudent ? '1' : '0' }}">
            <label for="student-id" class="form-label">Student Name</label>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <select name="student_ids[]" id="student-id" class="form-select tom-select">
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" @selected((string)($baseStudentId ?? '') === (string)$student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>
                @unless($isStudent)
                    <button type="button" class="btn btn-outline-secondary" id="add-student-btn">+</button>
                @endunless
            </div>
            <div id="additional-students" class="mt-3 d-flex flex-column gap-2">
                @foreach($defaultStudentIds->slice(1) as $studentId)
                    <div class="d-flex gap-2 align-items-center additional-student">
                        <select name="student_ids[]" class="form-select tom-select student-select" data-selected="{{ $studentId }}"></select>
                        <button type="button" class="btn btn-outline-danger remove-student">-</button>
                    </div>
                @endforeach
            </div>
            @unless($isStudent)
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="apply-missing" name="apply_missing" value="1" @checked(old('apply_missing'))>
                    <label class="form-check-label" for="apply-missing">Apply to all students who do not yet have the application</label>
                </div>
            @endunless
        </div>

        <div>
            <label for="institution-id" class="form-label">Institution Name</label>
            <select name="institution_id" id="institution-id" class="form-select tom-select">
                <option value="">Select institution</option>
                @foreach($institutions as $institution)
                    <option value="{{ $institution->id }}" @selected((string)old('institution_id') === (string)$institution->id)>{{ $institution->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="period-id" class="form-label">Period</label>
            <select name="period_id" id="period-id" class="form-select tom-select" data-tom-allow-empty="true">
                <option value="">Select period</option>
                @foreach($periods as $period)
                    <option value="{{ $period['id'] }}" @selected((string)old('period_id') === (string)$period['id'])>{{ $period['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" class="form-label">Status Application</label>
            <select name="status" id="status" class="form-select">
                <option value="">Select status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>

        @if($canSetStudentAccess)
            <div>
                <label class="form-label d-block">Student Access</label>
                @php($studentAccess = old('student_access', 'any'))
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="student_access" id="student-access-true-create" value="true" @checked($studentAccess === 'true')>
                    <label class="form-check-label" for="student-access-true-create">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="student_access" id="student-access-false-create" value="false" @checked($studentAccess === 'false')>
                    <label class="form-check-label" for="student-access-false-create">False</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="student_access" id="student-access-any-create" value="any" @checked($studentAccess === 'any')>
                    <label class="form-check-label" for="student-access-any-create">Any</label>
                </div>
            </div>
        @endif

        <div>
            <label for="submitted-at" class="form-label">Submitted At</label>
            <input type="date" name="submitted_at" id="submitted-at" class="form-control" value="{{ old('submitted_at', now()->format('Y-m-d')) }}">
        </div>

        <div>
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" id="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ url('/applications') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>
@push('scripts')
<script>
(() => {
    const studentSection = document.getElementById('student-section');
    if (!studentSection) {
        return;
    }

    const baseSelect = document.getElementById('student-id');
    const addBtn = document.getElementById('add-student-btn');
    const container = document.getElementById('additional-students');
    const applyMissing = document.getElementById('apply-missing');
    const allStudents = @json($students->map(fn ($student) => [
        'id' => $student->id,
        'name' => $student->name,
    ])->values());
    const missingStudents = @json($studentsWithoutApplication->map(fn ($student) => [
        'id' => $student->id,
        'name' => $student->name,
    ])->values());

    function destroyTomSelectInstance(select) {
        if (select && select.tomselect) {
            select.tomselect.destroy();
        }
    }

    function getSelectedValues(exclude = null) {
        const values = [];
        if (baseSelect && baseSelect.value && (!exclude || exclude !== baseSelect)) {
            values.push(String(baseSelect.value));
        }
        container.querySelectorAll('select.student-select').forEach((select) => {
            if (exclude && exclude === select) {
                return;
            }
            if (select.value) {
                values.push(String(select.value));
            }
        });
        return values;
    }

    function populateSelectOptions(select) {
        if (!select) {
            return;
        }

        const previous = select.dataset.current || select.value || '';
        const selectedValues = new Set(getSelectedValues(select).map(String));
        let desired = previous;
        if (desired && selectedValues.has(desired)) {
            desired = '';
        }

        destroyTomSelectInstance(select);
        select.innerHTML = '';

        allStudents.forEach((student) => {
            const value = String(student.id);
            if (selectedValues.has(value)) {
                return;
            }
            const option = document.createElement('option');
            option.value = student.id;
            option.textContent = student.name;
            select.appendChild(option);
        });

        if (desired && Array.from(select.options).some((option) => option.value === desired)) {
            select.value = desired;
        } else if (!select.value && select.options.length) {
            select.value = select.options[0].value;
        } else if (!select.options.length) {
            select.value = '';
        }

        select.dataset.current = select.value || '';
        window.initTomSelect && window.initTomSelect();
    }

    function updateAddButtonState() {
        if (!addBtn) {
            return;
        }
        const selected = new Set(getSelectedValues().map(String));
        const hasRemaining = allStudents.some((student) => !selected.has(String(student.id)));
        addBtn.disabled = !hasRemaining;
    }

    function populateAllSelects() {
        if (baseSelect) {
            populateSelectOptions(baseSelect);
        }
        container.querySelectorAll('select.student-select').forEach(populateSelectOptions);
        updateAddButtonState();
    }

    function createSelect(preselected = null) {
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex gap-2 align-items-center additional-student';
        const select = document.createElement('select');
        select.name = 'student_ids[]';
        select.className = 'form-select tom-select student-select';
        select.dataset.current = preselected ? String(preselected) : '';
        if (preselected) {
            select.dataset.selected = String(preselected);
        }
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-outline-danger remove-student';
        removeBtn.textContent = '-';
        wrapper.appendChild(select);
        wrapper.appendChild(removeBtn);
        container.appendChild(wrapper);
        populateAllSelects();
        return select;
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

    if (baseSelect) {
        baseSelect.dataset.current = baseSelect.value || '';
        baseSelect.addEventListener('change', () => {
            baseSelect.dataset.current = baseSelect.value || '';
            populateAllSelects();
        });
    }

    if (addBtn) {
        addBtn.addEventListener('click', () => {
            createSelect();
        });
    }

    if (applyMissing) {
        applyMissing.addEventListener('change', () => {
            if (!applyMissing.checked) {
                updateAddButtonState();
                return;
            }
            const selectedValues = new Set(getSelectedValues().map(String));
            missingStudents.forEach((student) => {
                const id = String(student.id);
                if (selectedValues.has(id)) {
                    return;
                }
                createSelect(student.id);
                selectedValues.add(id);
            });
            populateAllSelects();
        });
    }

    container.querySelectorAll('select.student-select').forEach((select) => {
        if (select.dataset.selected) {
            select.dataset.current = select.dataset.selected;
        }
    });

    populateAllSelects();
})();
</script>
@endpush
@endsection
