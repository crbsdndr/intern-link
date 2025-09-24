@php
    $internshipOptions = collect($internships);
    $baseInternshipId = old('internship_id');
    if (! $baseInternshipId && isset($log)) {
        $baseInternshipId = $log->internship_id ?? null;
    }
    $baseInternshipId = $baseInternshipId !== null && $baseInternshipId !== '' ? (int) $baseInternshipId : null;

    if (isset($log) && $baseInternshipId && $internshipOptions->where('id', $baseInternshipId)->isEmpty()) {
        $internshipOptions = $internshipOptions->push([
            'id' => $baseInternshipId,
            'label' => ($log->student_name ?? 'Unknown') . ' - ' . ($log->institution_name ?? 'Unknown'),
            'institution_id' => (int) ($log->institution_id ?? 0),
        ]);
    }

    $internshipOptions = $internshipOptions->unique('id')->values();

    $additionalOld = collect(old('additional_internship_ids', []))
        ->map(fn ($value) => (int) $value)
        ->filter(fn ($value) => $value !== $baseInternshipId)
        ->values();

    $applyToAll = old('apply_to_all', false);
@endphp

<form action="{{ $action }}" method="POST" class="card">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif
    <div class="card-body d-flex flex-column gap-3">
        @include('components.form-errors')

        <div id="internship-section"
             data-internships='@json($internshipOptions)'
             data-base-id="{{ $baseInternshipId ?? '' }}"
             data-is-update="{{ $method === 'PUT' ? '1' : '0' }}">
            <label class="form-label" for="base-internship">Internship</label>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <select name="internship_id" id="base-internship" class="form-select tom-select" data-placeholder="Select internship" {{ $method === 'PUT' ? 'disabled' : '' }}>
                    <option value="">Select internship</option>
                    @foreach($internshipOptions as $option)
                        <option value="{{ $option['id'] }}" @selected((string)($baseInternshipId ?? '') === (string)$option['id'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-outline-secondary" id="add-internship-btn">+</button>
            </div>
            @if($method === 'PUT' && $baseInternshipId)
                <input type="hidden" name="internship_id" value="{{ $baseInternshipId }}">
            @endif
            <div id="additional-internships" class="mt-3 d-flex flex-column gap-2">
                @foreach($additionalOld as $internshipId)
                    <div class="d-flex gap-2 align-items-center additional-internship">
                        <select name="additional_internship_ids[]" class="form-select tom-select additional-select" data-selected="{{ $internshipId }}"></select>
                        <button type="button" class="btn btn-outline-danger remove-internship">-</button>
                    </div>
                @endforeach
            </div>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="apply_to_all" id="apply-to-all" value="1" @checked(old('apply_to_all', false))>
                <label class="form-check-label" for="apply-to-all">Apply this to all company IDs that match the selected Internship (This will not affect existing ones)</label>
            </div>
        </div>

        @php($defaultLogDate = old('log_date'))
        @if(!$defaultLogDate && isset($log) && $log->log_date)
            @php($defaultLogDate = \Illuminate\Support\Carbon::parse($log->log_date)->format('Y-m-d'))
        @endif
        <div>
            <label class="form-label" for="log-date">Log Date</label>
            <input type="date" name="log_date" id="log-date" class="form-control" value="{{ $defaultLogDate }}">
        </div>

        <div>
            <label class="form-label" for="type">Type</label>
            <select name="type" id="type" class="form-select">
                <option value="">Select type</option>
                @foreach($types as $type)
                    <option value="{{ $type }}" @selected(old('type', isset($log) ? $log->log_type ?? $log->type ?? null : null) === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label" for="title">Title</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $log->title ?? '') }}" maxlength="150">
        </div>

        <div>
            <label class="form-label" for="content">Content</label>
            <textarea name="content" id="content" class="form-control" rows="5" required>{{ old('content', $log->content ?? '') }}</textarea>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ url('/monitorings') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

@push('scripts')
@once
<script>
(() => {
    const section = document.getElementById('internship-section');
    if (!section) {
        return;
    }

    const internships = JSON.parse(section.dataset.internships || '[]');
    const baseSelect = document.getElementById('base-internship');
    const addButton = document.getElementById('add-internship-btn');
    const container = document.getElementById('additional-internships');
    const applyAll = document.getElementById('apply-to-all');
    const isUpdate = section.dataset.isUpdate === '1';

    function destroyTom(select) {
        if (select && select.tomselect) {
            select.tomselect.destroy();
        }
    }

    function getOptionById(id) {
        return internships.find((item) => Number(item.id) === Number(id)) || null;
    }

    function getBaseInstitutionId() {
        const value = baseSelect.value;
        if (!value) {
            return null;
        }
        const option = getOptionById(value);
        return option ? Number(option.institution_id) : null;
    }

    function getSelectedIds(exclude) {
        const ids = [];
        if (baseSelect.value && baseSelect !== exclude) {
            ids.push(Number(baseSelect.value));
        }
        container.querySelectorAll('select.additional-select').forEach((select) => {
            if (select !== exclude && select.value) {
                ids.push(Number(select.value));
            }
        });
        return ids;
    }

    function availableInternships(institutionId, excludeSelect) {
        const selectedIds = new Set(getSelectedIds(excludeSelect));
        return internships.filter((item) => {
            if (Number(item.institution_id) !== Number(institutionId)) {
                return false;
            }
            return !selectedIds.has(Number(item.id));
        });
    }

    function refreshSelectOptions(select, institutionId) {
        if (!select) {
            return;
        }

        const previousValue = select.value || select.dataset.selected || '';
        destroyTom(select);
        select.innerHTML = '';

        const options = availableInternships(institutionId, select);
        options.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.label;
            select.appendChild(option);
        });

        if (previousValue && options.some((item) => String(item.id) === String(previousValue))) {
            select.value = previousValue;
        } else if (!select.value && options.length) {
            select.value = options[0].id;
        }

        select.dataset.selected = select.value || '';
        window.initTomSelect && window.initTomSelect();
    }

    function updateAdditionalSelects() {
        const institutionId = getBaseInstitutionId();
        container.querySelectorAll('select.additional-select').forEach((select) => {
            if (!institutionId) {
                destroyTom(select);
                select.innerHTML = '';
                select.dataset.selected = '';
                window.initTomSelect && window.initTomSelect();
                return;
            }
            refreshSelectOptions(select, institutionId);
        });
        updateAddButtonState();
    }

    function updateAddButtonState() {
        const institutionId = getBaseInstitutionId();
        if (!addButton) {
            return;
        }
        if (!institutionId || applyAll.checked) {
            addButton.setAttribute('disabled', 'disabled');
            return;
        }
        const hasRemaining = availableInternships(institutionId).length > 0;
        if (hasRemaining) {
            addButton.removeAttribute('disabled');
        } else {
            addButton.setAttribute('disabled', 'disabled');
        }
    }

    function addAdditionalSelect(preselected) {
        const institutionId = getBaseInstitutionId();
        if (!institutionId) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex gap-2 align-items-center additional-internship';

        const select = document.createElement('select');
        select.name = 'additional_internship_ids[]';
        select.className = 'form-select tom-select additional-select';
        if (preselected) {
            select.dataset.selected = String(preselected);
        }

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-outline-danger remove-internship';
        removeBtn.textContent = '-';
        removeBtn.addEventListener('click', () => {
            destroyTom(select);
            wrapper.remove();
            updateAddButtonState();
        });

        wrapper.appendChild(select);
        wrapper.appendChild(removeBtn);
        container.appendChild(wrapper);

        refreshSelectOptions(select, institutionId);
        if (preselected) {
            select.value = preselected;
            select.dataset.selected = String(preselected);
            window.initTomSelect && window.initTomSelect();
        }
        updateAddButtonState();
    }

    addButton.addEventListener('click', () => {
        addAdditionalSelect(null);
    });

    baseSelect.addEventListener('change', () => {
        updateAdditionalSelects();
    });

    applyAll.addEventListener('change', () => {
        updateAddButtonState();
    });

    const initialSelections = Array.from(container.querySelectorAll('select.additional-select'))
        .map((select) => Number(select.dataset.selected || select.value || 0))
        .filter((value) => !Number.isNaN(value) && value !== 0);
    container.innerHTML = '';
    initialSelections.forEach((value) => addAdditionalSelect(value));

    if (isUpdate && !baseSelect.value && section.dataset.baseId) {
        const stored = section.dataset.baseId;
        const option = getOptionById(stored);
        if (option) {
            baseSelect.value = stored;
        }
    }

    window.initTomSelect && window.initTomSelect();
    updateAdditionalSelects();
})();
</script>
@endonce
@endpush
