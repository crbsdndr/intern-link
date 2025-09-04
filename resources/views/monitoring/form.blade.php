<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')

    <div class="mb-3">
        <label class="form-label">Internship</label>
        <select name="internship_id" class="form-select searchable" {{ $readonly ? 'disabled' : '' }}>
            @foreach($internships as $internship)
                <option value="{{ $internship->id }}" {{ old('internship_id', optional($log)->internship_id) == $internship->id ? 'selected' : '' }}>{{ $internship->student_name }} – {{ $internship->institution_name }}</option>
            @endforeach
        </select>
        @if($readonly)
            <input type="hidden" name="internship_id" value="{{ old('internship_id', optional($log)->internship_id) }}">
        @endif
    </div>

    <div class="mb-3">
        <label class="form-label">Tanggal</label>
        <input type="date" name="log_date" class="form-control" value="{{ old('log_date', optional($log)->log_date ? \Illuminate\Support\Carbon::parse($log->log_date)->format('Y-m-d') : '') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Supervisor</label>
        <select name="supervisor_id" class="form-select searchable">
            <option value="">—</option>
            @foreach($supervisors as $supervisor)
                <option value="{{ $supervisor->id }}" {{ old('supervisor_id', optional($log)->supervisor_id) == $supervisor->id ? 'selected' : '' }}>{{ $supervisor->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Tipe</label>
        <select name="type" class="form-select searchable">
            @foreach($types as $type)
                <option value="{{ $type }}" {{ old('type', optional($log)->log_type ?? optional($log)->type) == $type ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Skor</label>
        <input type="number" name="score" class="form-control" value="{{ old('score', optional($log)->score) }}" min="0" max="100">
    </div>

    <div class="mb-3">
        <label class="form-label">Judul</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', optional($log)->title) }}" maxlength="150">
    </div>

    <div class="mb-3">
        <label class="form-label">Isi Laporan</label>
        <textarea name="content" class="form-control" rows="5" required>{{ old('content', optional($log)->content) }}</textarea>
    </div>

    <a href="/monitoring" class="btn btn-secondary">Back</a>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
