<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')

    <div class="mb-3">
        <label class="form-label">Applications</label>
        <div id="application-rows">
            <div class="d-flex mb-2 app-row">
                <select class="form-select tom-select first-application"></select>
                <button type="button" class="btn btn-danger ms-2 remove-row d-none">-</button>
            </div>
        </div>
        <button type="button" class="btn btn-secondary mt-2" id="add-application">+</button>
        <div class="form-check mt-2">
            <input type="checkbox" class="form-check-input" id="take-all">
            <label class="form-check-label" for="take-all">Ambil semua se-institusi</label>
        </div>
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
    const apps = @json($applications);
    const selectedInitial = @json(old('application_ids', $selected ?? []));
    const lockedFirst = @json($lockedFirst ?? false);
    const container = document.getElementById('application-rows');
    const addBtn = document.getElementById('add-application');
    const takeAll = document.getElementById('take-all');
    const firstRow = container.querySelector('.app-row');
    const firstSelect = firstRow.querySelector('select');
    let firstHidden = null;

    function optionLabel(app){
        return app.student_name + ' – ' + app.institution_name;
    }

    function getSelectedIds(){
        return Array.from(container.querySelectorAll('select'))
            .map(s => parseInt(s.value))
            .filter(id => !isNaN(id));
    }

    function refreshOptions(){
        const ids = getSelectedIds();
        const selectedSet = new Set(ids);
        const baseApp = apps.find(a => a.id === parseInt(firstSelect.value));
        const baseInst = baseApp ? baseApp.institution_id : null;

        container.querySelectorAll('select').forEach((sel, idx) => {
            const current = sel.value ? parseInt(sel.value) : null;
            const opts = apps.filter(a => {
                if (idx === 0) {
                    return true;
                }
                if (baseInst === null) return false;
                if (a.institution_id !== baseInst) return false;
                if (selectedSet.has(a.id) && a.id !== current) return false;
                return true;
            });
            const ts = sel.tomselect;
            if (ts) {
                ts.clearOptions();
                ts.addOptions(opts.map(app => ({ value: app.id, text: optionLabel(app) })));
                ts.refreshOptions(false);
                if (current && opts.find(a => a.id === current)) {
                    ts.setValue(current, false);
                } else {
                    ts.clear(true);
                }
            }
        });

        const disableFirst = container.children.length > 1 || takeAll.checked || lockedFirst;
        firstSelect.disabled = disableFirst;
        if (firstSelect.tomselect) {
            disableFirst ? firstSelect.tomselect.disable() : firstSelect.tomselect.enable();
        }
        if (disableFirst) {
            if (!firstHidden) {
                firstHidden = document.createElement('input');
                firstHidden.type = 'hidden';
                firstHidden.name = 'application_ids[]';
                firstRow.appendChild(firstHidden);
            }
            firstHidden.value = firstSelect.value || '';
            firstSelect.name = '';
        } else {
            if (firstHidden) {
                firstHidden.remove();
                firstHidden = null;
            }
            firstSelect.name = 'application_ids[]';
        }

        if (baseInst === null) {
            addBtn.disabled = true;
        } else {
            const remaining = apps.filter(a => a.institution_id === baseInst && !selectedSet.has(a.id));
            addBtn.disabled = remaining.length === 0 || takeAll.checked;
        }
    }

    function createRow(value){
        const row = document.createElement('div');
        row.className = 'd-flex mb-2 app-row';
        const sel = document.createElement('select');
        sel.name = 'application_ids[]';
        sel.className = 'form-select tom-select';
        sel.addEventListener('change', () => { refreshOptions(); });
        row.appendChild(sel);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-danger ms-2 remove-row';
        btn.textContent = '-';
        btn.addEventListener('click', () => { row.remove(); refreshOptions(); });
        row.appendChild(btn);
        container.appendChild(row);
        window.initTomSelect();
        refreshOptions();
        if (value) {
            sel.tomselect.setValue(value);
            refreshOptions();
        }
    }

    if (lockedFirst) {
        firstSelect.disabled = true;
        firstSelect.name = '';
        firstHidden = document.createElement('input');
        firstHidden.type = 'hidden';
        firstHidden.name = 'application_ids[]';
        firstRow.appendChild(firstHidden);
    }

    firstSelect.addEventListener('change', () => { refreshOptions(); });

    window.addEventListener('load', () => {
        window.initTomSelect();
        refreshOptions();
        if (selectedInitial[0]) {
            firstSelect.tomselect.setValue(selectedInitial[0]);
            refreshOptions();
        }
        for (let i = 1; i < selectedInitial.length; i++) {
            createRow(selectedInitial[i]);
        }
    });

    addBtn.addEventListener('click', () => {
        createRow(null);
    });

    takeAll.addEventListener('change', () => {
        if (takeAll.checked) {
            refreshOptions();
            const ids = getSelectedIds();
            const baseApp = apps.find(a => a.id === parseInt(firstSelect.value));
            const baseInst = baseApp ? baseApp.institution_id : null;
            if (baseInst !== null) {
                const remaining = apps.filter(a => a.institution_id === baseInst && !ids.includes(a.id));
                remaining.forEach(app => createRow(app.id));
            }
            addBtn.disabled = true;
        }
        refreshOptions();
    });
</script>
