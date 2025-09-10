<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')

    @php
        $allApplications = collect($applications);
        if(isset($internship)){
            $allApplications = $allApplications->prepend((object)[
                'id' => $internship->application_id,
                'student_name' => $internship->student_name,
                'institution_name' => $internship->institution_name,
                'institution_id' => $internship->institution_id,
            ]);
        }
        $allApplicationsData = $allApplications->map(function ($a) {
            return [
                'id' => $a->id,
                'student_name' => $a->student_name,
                'institution_name' => $a->institution_name,
                'institution_id' => $a->institution_id,
            ];
        });
    @endphp

    <div id="applications-wrapper">
        <div class="mb-3 application-item d-flex align-items-start">
            <div class="flex-grow-1">
                <label class="form-label">Application</label>
                <select name="application_ids[]" class="form-select tom-select" {{ $readonly ? 'disabled' : '' }}>
                    @foreach($allApplications as $application)
                        <option value="{{ $application->id }}" {{ old('application_ids.0', optional($internship)->application_id) == $application->id ? 'selected' : '' }}>{{ $application->student_name }} – {{ $application->institution_name }}</option>
                    @endforeach
                </select>
                @if($readonly)
                    <input type="hidden" name="application_ids[]" value="{{ old('application_ids.0', optional($internship)->application_id) }}">
                @endif
            </div>
        </div>
    </div>
    <button type="button" id="add-application" class="btn btn-secondary mb-3">+</button>
    <template id="application-template">
        <div class="mb-3 application-item d-flex align-items-start">
            <div class="flex-grow-1">
                <label class="form-label">Application</label>
                <select name="application_ids[]" class="form-select tom-select">
                    @foreach($applications as $application)
                        <option value="{{ $application->id }}">{{ $application->student_name }} – {{ $application->institution_name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn btn-danger ms-2 remove-application">-</button>
        </div>
    </template>
    <div class="form-check mb-3" id="apply-all-wrapper" style="display:none;">
        <input type="checkbox" class="form-check-input" id="apply-all" name="apply_all" value="1">
        <label class="form-check-label" for="apply-all">Select all applications from this institution</label>
    </div>

    <div class="mb-3">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($internship)->start_date ? \Illuminate\Support\Carbon::parse($internship->start_date)->format('Y-m-d') : '') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($internship)->end_date ? \Illuminate\Support\Carbon::parse($internship->end_date)->format('Y-m-d') : '') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach($statuses as $status)
                <option value="{{ $status }}" {{ old('status', optional($internship)->status) == $status ? 'selected' : '' }}>{{ $status }}</option>
            @endforeach
        </select>
    </div>

    <a href="/internship" class="btn btn-secondary">Back</a>
    <button type="submit" class="btn btn-primary">Save</button>
</form>

<script>
const wrapper = document.getElementById('applications-wrapper');
const addBtn = document.getElementById('add-application');
const allApps = @json($allApplicationsData);
const applyAll = document.getElementById('apply-all');
const applyAllWrapper = document.getElementById('apply-all-wrapper');

function syncHidden(select){
    let hidden = select.parentNode.querySelector('input[type="hidden"][name="application_ids[]"]');
    if(select.disabled){
        if(!hidden){
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'application_ids[]';
            select.parentNode.appendChild(hidden);
        }
        hidden.value = select.value;
        select.removeAttribute('name');
    }else{
        if(hidden) hidden.remove();
        select.name = 'application_ids[]';
    }
}

function updateUI(){
    let selects = wrapper.querySelectorAll('select[name="application_ids[]"]');
    const firstSelect = selects[0];
    const firstApp = allApps.find(a => String(a.id) === firstSelect.value);
    const institutionId = firstApp ? String(firstApp.institution_id) : null;
    const selectedIds = Array.from(selects).map(s => s.value);

    selects.forEach((select, idx) => {
        const current = select.value;
        let options = [];
        if(idx === 0){
            options = allApps;
        }else if(institutionId){
            options = allApps.filter(a => String(a.institution_id) === institutionId);
        }
        if(idx > 0){
            options = options.filter(a => !selectedIds.includes(String(a.id)) || String(a.id) === current);
        }
        if(select.tomselect){
            select.tomselect.destroy();
        }
        select.innerHTML = '';
        options.forEach(app => {
            const opt = document.createElement('option');
            opt.value = app.id;
            opt.textContent = `${app.student_name} – ${app.institution_name}`;
            if(String(app.id) === current) opt.selected = true;
            select.appendChild(opt);
        });
    });
    window.initTomSelect();

    const remaining = institutionId
        ? allApps.filter(a => String(a.institution_id) === institutionId && !selectedIds.includes(String(a.id)))
        : [];
    addBtn.disabled = !institutionId || remaining.length === 0 || (applyAll && applyAll.checked);

    if(applyAll){
        applyAllWrapper.style.display = institutionId ? 'block' : 'none';
        if(applyAll.checked){
            wrapper.querySelectorAll('.application-item').forEach((item, idx) => {
                if(idx > 0) item.remove();
            });
            remaining.forEach(app => {
                const tpl = document.getElementById('application-template');
                const clone = tpl.content.cloneNode(true);
                const sel = clone.querySelector('select');
                sel.value = app.id;
                wrapper.appendChild(clone);
            });
            selects = wrapper.querySelectorAll('select[name="application_ids[]"]');
            selects.forEach(sel => { sel.disabled = true; syncHidden(sel); });
            window.initTomSelect();
            return;
        }
    }

    selects.forEach((sel, idx) => {
        sel.disabled = idx === 0 && selects.length > 1;
        syncHidden(sel);
    });
}

addBtn.addEventListener('click', () => {
    const tpl = document.getElementById('application-template');
    const clone = tpl.content.cloneNode(true);
    const firstSelect = wrapper.querySelector('select[name="application_ids[]"]');
    const firstApp = allApps.find(a => String(a.id) === firstSelect.value);
    if(!firstApp) return;
    const selected = Array.from(wrapper.querySelectorAll('select[name="application_ids[]"]')).map(s => s.value);
    const remaining = allApps.filter(a => String(a.institution_id) === String(firstApp.institution_id) && !selected.includes(String(a.id)));
    if(remaining.length === 0) return;
    const newSelect = clone.querySelector('select');
    newSelect.value = remaining[0].id;
    wrapper.appendChild(clone);
    updateUI();
});

wrapper.addEventListener('click', e => {
    if(e.target.classList.contains('remove-application')){
        e.target.closest('.application-item').remove();
        if(applyAll && applyAll.checked && wrapper.querySelectorAll('.application-item').length === 1){
            applyAll.checked = false;
        }
        updateUI();
    }
});

wrapper.addEventListener('change', e => {
    if(e.target.matches('select[name="application_ids[]"]')){
        updateUI();
    }
});

if(applyAll){
    applyAll.addEventListener('change', updateUI);
}

updateUI();
</script>
