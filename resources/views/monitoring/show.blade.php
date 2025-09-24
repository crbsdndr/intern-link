@extends('layouts.app')

@section('title', 'Monitoring Details')

@section('content')
@php($role = session('role'))
@php($studentPhoto = data_get($log, 'student_photo'))
@php($institutionPhoto = data_get($log, 'institution_photo'))
@php($formatBoolean = function ($value) {
    if ($value === null) {
        return '—';
    }
    $normalized = strtolower((string) $value);
    return in_array($normalized, ['1', 'true', 't', 'yes'], true) ? 'True' : 'False';
})
<h1 class="mb-4">Monitoring Details</h1>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Student Information</div>
            <div class="card-body">
                <div class="row g-3 align-items-start">
                    <div class="col-sm-4">
                        @if($studentPhoto)
                            <img src="{{ $studentPhoto }}" alt="{{ $log->student_name }}" class="img-fluid rounded border">
                        @else
                            <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="aspect-ratio: 3 / 4;">
                                <span class="text-muted">No Photo</span>
                            </div>
                        @endif
                    </div>
                    <div class="col-sm-8">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Student Name</dt>
                            <dd class="col-sm-7">
                                @if($log->student_id)
                                    <a href="/students/{{ $log->student_id }}/read">{{ $log->student_name }}</a>
                                @else
                                    {{ $log->student_name }}
                                @endif
                            </dd>
                            <dt class="col-sm-5">Student Email</dt>
                            <dd class="col-sm-7">{{ $log->student_email ?? '—' }}</dd>
                            <dt class="col-sm-5">Student Phone</dt>
                            <dd class="col-sm-7">{{ $log->student_phone ?? '—' }}</dd>
                            <dt class="col-sm-5">Student Number</dt>
                            <dd class="col-sm-7">{{ $log->student_number ?? '—' }}</dd>
                            <dt class="col-sm-5">National Student Number</dt>
                            <dd class="col-sm-7">{{ $log->national_sn ?? '—' }}</dd>
                            <dt class="col-sm-5">Student Major</dt>
                            <dd class="col-sm-7">{{ $log->student_major ?? '—' }}</dd>
                            <dt class="col-sm-5">Student Class</dt>
                            <dd class="col-sm-7">{{ $log->student_class ?? '—' }}</dd>
                            <dt class="col-sm-5">Student Batch</dt>
                            <dd class="col-sm-7">{{ $log->student_batch ?? '—' }}</dd>
                            <dt class="col-sm-5">Student Notes</dt>
                            <dd class="col-sm-7">{{ $log->student_notes ?? '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Institution Information</div>
            <div class="card-body">
                <div class="row g-3 align-items-start">
                    <div class="col-sm-4">
                        @if($institutionPhoto)
                            <img src="{{ $institutionPhoto }}" alt="{{ $log->institution_name }}" class="img-fluid rounded border">
                        @else
                            <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="aspect-ratio: 16 / 9;">
                                <span class="text-muted">No Photo</span>
                            </div>
                        @endif
                    </div>
                    <div class="col-sm-8">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Institution Name</dt>
                            <dd class="col-sm-7">
                                @if($log->institution_id)
                                    <a href="/institutions/{{ $log->institution_id }}/read">{{ $log->institution_name }}</a>
                                @else
                                    {{ $log->institution_name }}
                                @endif
                            </dd>
                            <dt class="col-sm-5">Institution Address</dt>
                            <dd class="col-sm-7">{{ $log->institution_address ?? '—' }}</dd>
                            <dt class="col-sm-5">Institution City</dt>
                            <dd class="col-sm-7">{{ $log->institution_city ?? '—' }}</dd>
                            <dt class="col-sm-5">Institution Province</dt>
                            <dd class="col-sm-7">{{ $log->institution_province ?? '—' }}</dd>
                            <dt class="col-sm-5">Institution Website</dt>
                            <dd class="col-sm-7">
                                @if($log->institution_website)
                                    <a href="{{ $log->institution_website }}" target="_blank" rel="noopener">{{ $log->institution_website }}</a>
                                @else
                                    —
                                @endif
                            </dd>
                            <dt class="col-sm-5">Institution Industry</dt>
                            <dd class="col-sm-7">{{ $log->institution_industry ?? '—' }}</dd>
                            <dt class="col-sm-5">Institution Notes</dt>
                            <dd class="col-sm-7">{{ $log->institution_notes ?? '—' }}</dd>
                            <dt class="col-sm-5">Institution Contact Name</dt>
                            <dd class="col-sm-7">{{ $log->institution_contact_name ?? '—' }}</dd>
                            <dt class="col-sm-5">Institution Contact Email</dt>
                            <dd class="col-sm-7">{{ $log->institution_contact_email ?? '—' }}</dd>
                            <dt class="col-sm-5">Institution Contact Phone</dt>
                            <dd class="col-sm-7">{{ $log->institution_contact_phone ?? '—' }}</dd>
                            <dt class="col-sm-5">Institution Contact Position</dt>
                            <dd class="col-sm-7">{{ $log->institution_contact_position ?? '—' }}</dd>
                            <dt class="col-sm-5">Institution Contact Primary</dt>
                            <dd class="col-sm-7">{{ $formatBoolean($log->institution_contact_primary) }}</dd>
                            <dt class="col-sm-5">Institution Quota</dt>
                            <dd class="col-sm-7">{{ $log->institution_quota ?? '—' }}</dd>
                            <dt class="col-sm-5">Institution Quota Used</dt>
                            <dd class="col-sm-7">{{ $log->institution_quota_used ?? '—' }}</dd>
                            <dt class="col-sm-5">Institution Quota Period Year</dt>
                            <dd class="col-sm-7">{{ $log->institution_quota_period_year ?? '—' }}</dd>
                            <dt class="col-sm-5">Institution Quota Period Term</dt>
                            <dd class="col-sm-7">{{ $log->institution_quota_period_term ?? '—' }}</dd>
                            <dt class="col-sm-5">Institution Quota Notes</dt>
                            <dd class="col-sm-7">{{ $log->institution_quota_notes ?? '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Application Information</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Application Period Year</dt>
                    <dd class="col-sm-7">{{ $log->application_period_year ?? '—' }}</dd>
                    <dt class="col-sm-5">Application Period Term</dt>
                    <dd class="col-sm-7">{{ $log->application_period_term ?? '—' }}</dd>
                    <dt class="col-sm-5">Application Status Application</dt>
                    <dd class="col-sm-7">
                        @if($log->application_status)
                            {{ ucwords(str_replace('_', ' ', $log->application_status)) }}
                        @else
                            —
                        @endif
                    </dd>
                    <dt class="col-sm-5">Application Student Access</dt>
                    <dd class="col-sm-7">{{ $formatBoolean($log->application_student_access) }}</dd>
                    <dt class="col-sm-5">Application Submitted At</dt>
                    <dd class="col-sm-7">{{ $log->application_submitted_at ?? '—' }}</dd>
                    <dt class="col-sm-5">Application Notes</dt>
                    <dd class="col-sm-7">{{ $log->application_notes ?? '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Internship Information</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Internship Start Date</dt>
                    <dd class="col-sm-7">{{ $log->internship_start_date ?? '—' }}</dd>
                    <dt class="col-sm-5">Internship End Date</dt>
                    <dd class="col-sm-7">{{ $log->internship_end_date ?? '—' }}</dd>
                    <dt class="col-sm-5">Internship Status</dt>
                    <dd class="col-sm-7">
                        @if($log->internship_status)
                            {{ ucwords(str_replace('_', ' ', $log->internship_status)) }}
                        @else
                            —
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header fw-semibold">Monitoring Log</div>
    <div class="card-body d-flex flex-column gap-2">
        <div><span class="fw-semibold">Title:</span> {{ $log->title ?? '—' }}</div>
        <div><span class="fw-semibold">Log Date:</span> {{ $log->log_date ?? '—' }}</div>
        <div><span class="fw-semibold">Type:</span>
            @if($log->log_type)
                {{ ucwords(str_replace('_', ' ', $log->log_type)) }}
            @else
                —
            @endif
        </div>
        <div>
            <span class="fw-semibold">Content:</span>
            @if($log->content)
                <pre class="mt-2 mb-0" style="white-space: pre-wrap;">{{ $log->content }}</pre>
            @else
                <div class="mt-2 text-muted">—</div>
            @endif
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2">
    <a href="/monitorings" class="btn btn-secondary">Back</a>
    @if($role !== 'student')
        <a href="/monitorings/{{ $log->monitoring_log_id }}/update" class="btn btn-warning">Update</a>
        <form action="/monitorings/{{ $log->monitoring_log_id }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this monitoring?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger" type="submit">Delete</button>
        </form>
    @endif
</div>
@endsection
