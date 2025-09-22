@php($isEdit = ($method ?? 'POST') === 'PUT')
<form action="{{ $action }}" method="POST" class="d-flex flex-column gap-3">
    @csrf
    @if(($method ?? 'POST') === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <label class="form-label" for="institution-name">Name</label>
            <input type="text" id="institution-name" name="name" class="form-control" value="{{ old('name', optional($institution)->name) }}" @if($isEdit) disabled @endif>
        </div>
        <div class="col-12 col-lg-6">
            <label class="form-label" for="institution-photo">Photo</label>
            <input type="text" id="institution-photo" name="photo" class="form-control" value="{{ old('photo', optional($institution)->photo) }}" placeholder="https://example.com/photo.jpg">
        </div>
        <div class="col-12">
            <label class="form-label" for="institution-address">Address</label>
            <input type="text" id="institution-address" name="address" class="form-control" value="{{ old('address', optional($institution)->address) }}">
        </div>
        <div class="col-12 col-lg-6">
            <label class="form-label" for="institution-city">City</label>
            <select id="institution-city" name="city" class="form-select tom-select" data-tom-allow-empty="true">
                <option value="">Select city</option>
                @foreach($cities as $city)
                    <option value="{{ $city }}" @selected(old('city', optional($institution)->city) === $city)>{{ $city }}</option>
                @endforeach
                @php($currentCity = old('city', optional($institution)->city))
                @if($currentCity && !in_array($currentCity, $cities))
                    <option value="{{ $currentCity }}" selected>{{ $currentCity }}</option>
                @endif
            </select>
        </div>
        <div class="col-12 col-lg-6">
            <label class="form-label" for="institution-province">Province</label>
            <select id="institution-province" name="province" class="form-select tom-select" data-tom-allow-empty="true">
                <option value="">Select province</option>
                @foreach($provinces as $province)
                    <option value="{{ $province }}" @selected(old('province', optional($institution)->province) === $province)>{{ $province }}</option>
                @endforeach
                @php($currentProvince = old('province', optional($institution)->province))
                @if($currentProvince && !in_array($currentProvince, $provinces))
                    <option value="{{ $currentProvince }}" selected>{{ $currentProvince }}</option>
                @endif
            </select>
        </div>
        <div class="col-12 col-lg-6">
            <label class="form-label" for="institution-website">Website</label>
            <input type="text" id="institution-website" name="website" class="form-control" value="{{ old('website', optional($institution)->website) }}" placeholder="https://example.com">
        </div>
        <div class="col-12 col-lg-6">
            <label class="form-label" for="institution-industry">Industry</label>
            @php($currentIndustry = old('industry', optional($institution)->industry))
            <select id="institution-industry" name="industry" class="form-select tom-select" data-tom-create="true" data-tom-allow-empty="true">
                <option value="">Select industry</option>
                @foreach($industries as $industry)
                    <option value="{{ $industry }}" @selected($currentIndustry === $industry)>{{ $industry }}</option>
                @endforeach
                @if($currentIndustry && !in_array($currentIndustry, $industries))
                    <option value="{{ $currentIndustry }}" selected>{{ $currentIndustry }}</option>
                @endif
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="institution-notes">Notes</label>
            <textarea id="institution-notes" name="notes" class="form-control" rows="3">{{ old('notes', optional($institution)->notes) }}</textarea>
        </div>
    </div>

    <div class="border-top pt-3 mt-2">
        <h2 class="h5 mb-3">Primary Contact</h2>
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <label class="form-label" for="institution-contact-name">Contact Name</label>
                <input type="text" id="institution-contact-name" name="contact_name" class="form-control" value="{{ old('contact_name', optional($institution)->contact_name) }}">
            </div>
            <div class="col-12 col-lg-6">
                <label class="form-label" for="institution-contact-email">Contact E-Mail</label>
                <input type="email" id="institution-contact-email" name="contact_email" class="form-control" value="{{ old('contact_email', optional($institution)->contact_email) }}" placeholder="name@example.com">
            </div>
            <div class="col-12 col-lg-6">
                <label class="form-label" for="institution-contact-phone">Contact Phone</label>
                <input type="text" id="institution-contact-phone" name="contact_phone" class="form-control" value="{{ old('contact_phone', optional($institution)->contact_phone) }}" inputmode="numeric" pattern="[0-9]*">
            </div>
            <div class="col-12 col-lg-6">
                <label class="form-label" for="institution-contact-position">Contact Position</label>
                <input type="text" id="institution-contact-position" name="contact_position" class="form-control" value="{{ old('contact_position', optional($institution)->contact_position) }}">
            </div>
            @php($defaultPrimary = optional($institution)->contact_primary)
            @php($contactPrimaryValue = old('contact_primary', is_null($defaultPrimary) ? '' : ($defaultPrimary ? 'true' : 'false')))
            <div class="col-12">
                <span class="form-label d-block">Contact Is Primary?</span>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="contact_primary" id="contact-primary-any" value="" {{ $contactPrimaryValue === '' ? 'checked' : '' }}>
                    <label class="form-check-label" for="contact-primary-any">Any</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="contact_primary" id="contact-primary-true" value="true" {{ $contactPrimaryValue === 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="contact-primary-true">True</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="contact_primary" id="contact-primary-false" value="false" {{ $contactPrimaryValue === 'false' ? 'checked' : '' }}>
                    <label class="form-check-label" for="contact-primary-false">False</label>
                </div>
            </div>
        </div>
    </div>

    @php($selectedPeriod = old('period_selection', optional($institution)->period_id ? (string) optional($institution)->period_id : ''))
    <div class="border-top pt-3 mt-2">
        <h2 class="h5 mb-3">Period &amp; Quota</h2>
        <div class="row g-3">
            <div class="col-12 col-lg-6 @if($selectedPeriod === 'create_new') d-none @endif" id="period-select-wrapper">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0" for="period_selection">Period</label>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="period-create-button">Create new period</button>
                </div>
                <select
                    id="period_selection"
                    name="period_selection"
                    class="form-select tom-select"
                    data-tom-allow-empty="true"
                    @if($selectedPeriod === 'create_new') disabled @endif
                >
                    <option value="">Select period</option>
                    @foreach($periods as $period)
                        @php($value = (string) $period->id)
                        <option value="{{ $value }}" @selected($selectedPeriod === $value)>{{ $period->year }}: {{ $period->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-6">
                <label class="form-label" for="institution-quota">Quota</label>
                <input type="number" id="institution-quota" name="quota" class="form-control" value="{{ old('quota', optional($institution)->quota) }}" min="0" step="1">
            </div>
        </div>
        <div id="new-period-fields" class="row g-3 mt-1 @if($selectedPeriod !== 'create_new') d-none @endif">
            <div class="col-12 col-lg-6">
                <label class="form-label" for="new_period_year">New Period Year</label>
                <input type="number" id="new_period_year" name="new_period_year" class="form-control" value="{{ old('new_period_year') }}" min="1900" max="2100" step="1" @if($selectedPeriod !== 'create_new') disabled @endif>
            </div>
            <div class="col-12 col-lg-6">
                <label class="form-label" for="new_period_term">New Period Term</label>
                <input type="number" id="new_period_term" name="new_period_term" class="form-control" value="{{ old('new_period_term') }}" min="1" step="1" @if($selectedPeriod !== 'create_new') disabled @endif>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" id="period-select-return">Choose existing period</button>
            </div>
            @if($selectedPeriod === 'create_new')
                <input type="hidden" name="period_selection" id="period_selection_hidden" value="create_new">
            @endif
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-end border-top pt-3 mt-2">
        <a href="/institutions" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const periodSelect = document.getElementById('period_selection');
    if (!periodSelect) {
        return;
    }
    const wrapper = document.getElementById('period-select-wrapper');
    const newFields = document.getElementById('new-period-fields');
    const createButton = document.getElementById('period-create-button');
    const returnButton = document.getElementById('period-select-return');
    const yearInput = document.getElementById('new_period_year');
    const termInput = document.getElementById('new_period_term');
    const hiddenInputId = 'period_selection_hidden';
    const newPeriodInputs = [yearInput, termInput];

    const getHiddenInput = () => document.getElementById(hiddenInputId);

    const ensureHiddenInput = () => {
        let hidden = getHiddenInput();
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'period_selection';
            hidden.id = hiddenInputId;
            newFields?.appendChild(hidden);
        }
        hidden.value = 'create_new';
        return hidden;
    };

    const removeHiddenInput = () => {
        const hidden = getHiddenInput();
        if (hidden) {
            hidden.remove();
        }
    };

    const getTomWrapper = () => (periodSelect.tomselect ? periodSelect.tomselect.wrapper : null);

    const setNewInputsDisabled = (disabled) => {
        newPeriodInputs.forEach((input) => {
            if (!input) {
                return;
            }
            input.disabled = disabled;
        });
    };

    const disableSelectComponent = () => {
        if (periodSelect.tomselect) {
            periodSelect.tomselect.clear(true);
            periodSelect.tomselect.close();
            periodSelect.tomselect.disable();
        } else {
            periodSelect.value = '';
        }
        periodSelect.disabled = true;
    };

    const enableSelectComponent = (clearValue = false) => {
        periodSelect.disabled = false;
        if (periodSelect.tomselect) {
            periodSelect.tomselect.enable();
            if (clearValue) {
                periodSelect.tomselect.clear(true);
            }
        } else if (clearValue) {
            periodSelect.value = '';
        }
    };

    const enterNewMode = () => {
        ensureHiddenInput();
        disableSelectComponent();
        wrapper?.classList.add('d-none');
        getTomWrapper()?.classList.add('d-none');
        newFields?.classList.remove('d-none');
        setNewInputsDisabled(false);
        yearInput?.focus();
    };

    const enterExistingMode = ({ clearSelect = true } = {}) => {
        removeHiddenInput();
        enableSelectComponent(clearSelect);
        wrapper?.classList.remove('d-none');
        getTomWrapper()?.classList.remove('d-none');
        newFields?.classList.add('d-none');
        setNewInputsDisabled(true);
    };

    createButton?.addEventListener('click', (event) => {
        event.preventDefault();
        enterNewMode();
    });

    returnButton?.addEventListener('click', (event) => {
        event.preventDefault();
        enterExistingMode();
    });

    if (!newFields?.classList.contains('d-none')) {
        enterNewMode();
    } else {
        enterExistingMode({ clearSelect: false });
    }
});
</script>
@endpush
