<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')

    <div class="mb-3">
        <label class="form-label">Application</label>
        <select name="application_id" class="form-select" {{ ($applicationReadonly ?? false) ? 'disabled' : '' }}>
            @foreach($applications as $app)
                <option value="{{ $app->id }}" {{ old('application_id', optional($internship)->application_id) == $app->id ? 'selected' : '' }}>
                    {{ $app->student_name }} - {{ $app->institution_name }} ({{ $app->period_year }} {{ $app->period_term }})
                </option>
            @endforeach
        </select>
        @if($applicationReadonly ?? false)
            <input type="hidden" name="application_id" value="{{ old('application_id', optional($internship)->application_id) }}">
        @endif
    </div>

    <div class="mb-3">
        <label class="form-label">Student Name</label>
        <select name="student_id" class="form-select">
            @foreach($students as $student)
                <option value="{{ $student->id }}" {{ old('student_id', optional($internship)->student_id) == $student->id ? 'selected' : '' }}>
                    {{ $student->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Institution Name</label>
        <select name="institution_id" class="form-select">
            @foreach($institutions as $institution)
                <option value="{{ $institution->id }}" {{ old('institution_id', optional($internship)->institution_id) == $institution->id ? 'selected' : '' }}>
                    {{ $institution->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Year - Semester</label>
        <select name="period_id" class="form-select">
            @foreach($periods as $period)
                <option value="{{ $period->id }}" {{ old('period_id', optional($internship)->period_id) == $period->id ? 'selected' : '' }}>
                    {{ $period->year }} - {{ $period->term }}
                </option>
            @endforeach
        </select>
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
