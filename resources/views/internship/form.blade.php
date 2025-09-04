<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')

    <div class="mb-3">
        <label class="form-label">Application</label>
        <select name="application_id" class="form-select searchable" {{ $readonly ? 'disabled' : '' }}>
            @foreach($applications as $application)
                <option value="{{ $application->id }}" {{ old('application_id', optional($internship)->application_id) == $application->id ? 'selected' : '' }}>{{ $application->student_name }} – {{ $application->institution_name }}</option>
            @endforeach
        </select>
        @if($readonly)
            <input type="hidden" name="application_id" value="{{ old('application_id', optional($internship)->application_id) }}">
        @endif
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
