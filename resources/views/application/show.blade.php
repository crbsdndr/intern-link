@extends('layouts.app')

@section('title', 'Application Details')

@section('content')
<h1 class="mb-4">Application Details</h1>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Student Information</div>
            <div class="card-body d-flex flex-column gap-2">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="rounded overflow-hidden" style="width:96px;height:96px;background:#f8f9fa;">
                        @if($application->student_photo)
                            <img src="{{ $application->student_photo }}" alt="Student Photo" class="img-fluid w-100 h-100 object-fit-cover">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">No Photo</div>
                        @endif
                    </div>
                    <div>
                        <div class="fw-semibold">Student Name</div>
                        <a href="/students/{{ $application->student_id }}/read" class="text-decoration-none">{{ $application->student_name }}</a>
                    </div>
                </div>
                <div><span class="fw-semibold">Email:</span> {{ $application->student_email ?? '—' }}</div>
                <div><span class="fw-semibold">Phone:</span> {{ $application->student_phone ?? '—' }}</div>
                <div><span class="fw-semibold">Student Number:</span> {{ $application->student_number ?? '—' }}</div>
                <div><span class="fw-semibold">National Student Number:</span> {{ $application->national_sn ?? '—' }}</div>
                <div><span class="fw-semibold">Major:</span> {{ $application->student_major ?? '—' }}</div>
                <div><span class="fw-semibold">Class:</span> {{ $application->student_class ?? '—' }}</div>
                <div><span class="fw-semibold">Batch:</span> {{ $application->student_batch ?? '—' }}</div>
                <div><span class="fw-semibold">Notes:</span> {{ $application->student_notes ?? '—' }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Institution Information</div>
            <div class="card-body d-flex flex-column gap-2">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="rounded overflow-hidden" style="width:96px;height:96px;background:#f8f9fa;">
                        @if($application->institution_photo)
                            <img src="{{ $application->institution_photo }}" alt="Institution Photo" class="img-fluid w-100 h-100 object-fit-cover">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">No Photo</div>
                        @endif
                    </div>
                    <div>
                        <div class="fw-semibold">Institution Name</div>
                        <a href="/institutions/{{ $application->institution_id }}/read" class="text-decoration-none">{{ $application->institution_name }}</a>
                    </div>
                </div>
                <div><span class="fw-semibold">Address:</span> {{ $application->institution_address ?? '—' }}</div>
                <div><span class="fw-semibold">City:</span> {{ $application->institution_city ?? '—' }}</div>
                <div><span class="fw-semibold">Province:</span> {{ $application->institution_province ?? '—' }}</div>
                <div><span class="fw-semibold">Website:</span> {{ $application->institution_website ?? '—' }}</div>
                <div><span class="fw-semibold">Industry:</span> {{ $application->institution_industry ?? '—' }}</div>
                <div><span class="fw-semibold">Notes:</span> {{ $application->institution_notes ?? '—' }}</div>
                <div class="fw-semibold mt-2">Primary Contact</div>
                <div><span class="fw-semibold">Name:</span> {{ $application->institution_contact_name ?? '—' }}</div>
                <div><span class="fw-semibold">Email:</span> {{ $application->institution_contact_email ?? '—' }}</div>
                <div><span class="fw-semibold">Phone:</span> {{ $application->institution_contact_phone ?? '—' }}</div>
                <div><span class="fw-semibold">Position:</span> {{ $application->institution_contact_position ?? '—' }}</div>
                <div><span class="fw-semibold">Primary Contact:</span> {{ $application->institution_contact_primary ? 'True' : 'False' }}</div>
                <div class="fw-semibold mt-2">Quota Snapshot</div>
                <div><span class="fw-semibold">Quota:</span> {{ $application->institution_quota ?? '—' }}</div>
                <div><span class="fw-semibold">Quota Used:</span> {{ $application->institution_quota_used ?? '—' }}</div>
                <div><span class="fw-semibold">Quota Period Year:</span> {{ $application->institution_quota_period_year ?? '—' }}</div>
                <div><span class="fw-semibold">Quota Period Term:</span> {{ $application->institution_quota_period_term ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header fw-semibold">Application Information</div>
    <div class="card-body d-flex flex-column gap-2">
        <div><span class="fw-semibold">Period Year:</span> {{ $application->period_year }}</div>
        <div><span class="fw-semibold">Period Term:</span> {{ $application->period_term }}</div>
        <div><span class="fw-semibold">Status Application:</span> {{ ucwords(str_replace('_', ' ', $application->status)) }}</div>
        <div><span class="fw-semibold">Student Access:</span> {{ $application->student_access ? 'True' : 'False' }}</div>
        <div><span class="fw-semibold">Submitted At:</span> {{ \Illuminate\Support\Carbon::parse($application->submitted_at)->format('Y-m-d') }}</div>
        <div><span class="fw-semibold">Notes:</span> {{ $application->application_notes ?? '—' }}</div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="/applications" class="btn btn-outline-secondary">Back</a>
    <a href="/applications/{{ $application->id }}/update" class="btn btn-warning">Update</a>
</div>
@endsection
