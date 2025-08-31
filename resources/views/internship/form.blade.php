<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')

    <div class="mb-3">
        <label class="form-label">Application</label>
        <select name="application_id" class="form-select">
            <option value="">-- Select --</option>
            @foreach($applications as $app)
                <option value="{{ $app->id }}" {{ old('application_id', optional($internship)->application_id) == $app->id ? 'selected' : '' }}>
                    {{ $app->student_name }} - {{ $app->institution_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Period</label>
        <select name="period_id" class="form-select">
            <option value="">-- Select --</option>
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
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control">{{ old('notes', optional($internship)->notes) }}</textarea>
    </div>

    <a href="/internship" class="btn btn-secondary">Back</a>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
