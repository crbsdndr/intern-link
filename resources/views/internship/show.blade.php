@extends('layouts.app')

@section('title', 'Internship Details')

@section('content')
@php($isStudent = session('role') === 'student')

<div class="page-header">
    <h1>Internship Details</h1>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Student Information</div>
            <div class="card-body d-flex flex-column gap-2">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="rounded overflow-hidden" style="width:96px;height:96px;background:#f8f9fa;">
                        @if($internship->student_photo)
                            <img src="{{ $internship->student_photo }}" alt="Student Photo" class="img-fluid w-100 h-100 object-fit-cover">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">No Photo</div>
                        @endif
                    </div>
                    <div>
                        <div class="fw-semibold">Student Name</div>
                        <a href="/students/{{ $internship->student_id }}/read" class="text-decoration-none">{{ $internship->student_name }}</a>
                    </div>
                </div>
                <div><span class="fw-semibold">Email:</span> {{ $internship->student_email ?? '—' }}</div>
                <div><span class="fw-semibold">Phone:</span> {{ $internship->student_phone ?? '—' }}</div>
                <div><span class="fw-semibold">Student Number:</span> {{ $internship->student_number ?? '—' }}</div>
                <div><span class="fw-semibold">National Student Number:</span> {{ $internship->national_sn ?? '—' }}</div>
                <div><span class="fw-semibold">Major:</span> {{ $internship->student_major ?? '—' }}</div>
                <div><span class="fw-semibold">Class:</span> {{ $internship->student_class ?? '—' }}</div>
                <div><span class="fw-semibold">Batch:</span> {{ $internship->student_batch ?? '—' }}</div>
                <div><span class="fw-semibold">Notes:</span> {{ $internship->student_notes ?? '—' }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Institution Information</div>
            <div class="card-body d-flex flex-column gap-2">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="rounded overflow-hidden" style="width:96px;height:96px;background:#f8f9fa;">
                        @if($internship->institution_photo)
                            <img src="{{ $internship->institution_photo }}" alt="Institution Photo" class="img-fluid w-100 h-100 object-fit-cover">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">No Photo</div>
                        @endif
                    </div>
                    <div>
                        <div class="fw-semibold">Institution Name</div>
                        <a href="/institutions/{{ $internship->institution_id }}/read" class="text-decoration-none">{{ $internship->institution_name }}</a>
                    </div>
                </div>
                <div><span class="fw-semibold">Address:</span> {{ $internship->institution_address ?? '—' }}</div>
                <div><span class="fw-semibold">City:</span> {{ $internship->institution_city ?? '—' }}</div>
                <div><span class="fw-semibold">Province:</span> {{ $internship->institution_province ?? '—' }}</div>
                <div><span class="fw-semibold">Website:</span> {{ $internship->institution_website ?? '—' }}</div>
                <div><span class="fw-semibold">Industry:</span> {{ $internship->institution_industry ?? '—' }}</div>
                <div><span class="fw-semibold">Notes:</span> {{ $internship->institution_notes ?? '—' }}</div>
                <div class="fw-semibold mt-2">Primary Contact</div>
                <div><span class="fw-semibold">Name:</span> {{ $internship->institution_contact_name ?? '—' }}</div>
                <div><span class="fw-semibold">Email:</span> {{ $internship->institution_contact_email ?? '—' }}</div>
                <div><span class="fw-semibold">Phone:</span> {{ $internship->institution_contact_phone ?? '—' }}</div>
                <div><span class="fw-semibold">Position:</span> {{ $internship->institution_contact_position ?? '—' }}</div>
                <div><span class="fw-semibold">Primary Contact:</span> {{ $internship->institution_contact_primary ? 'True' : 'False' }}</div>
                <div class="fw-semibold mt-2">Quota Snapshot</div>
                <div><span class="fw-semibold">Quota:</span> {{ $internship->institution_quota ?? '—' }}</div>
                <div><span class="fw-semibold">Quota Used:</span> {{ $internship->institution_quota_used ?? '—' }}</div>
                <div><span class="fw-semibold">Quota Period Year:</span> {{ $internship->institution_quota_period_year ?? '—' }}</div>
                <div><span class="fw-semibold">Quota Period Term:</span> {{ $internship->institution_quota_period_term ?? '—' }}</div>
                <div><span class="fw-semibold">Quota Notes:</span> {{ $internship->institution_quota_notes ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Application Information</div>
            <div class="card-body d-flex flex-column gap-2">
                <div><span class="fw-semibold">Period Year:</span> {{ $internship->application_period_year }}</div>
                <div><span class="fw-semibold">Period Term:</span> {{ $internship->application_period_term }}</div>
                <div><span class="fw-semibold">Status Application:</span> {{ ucwords(str_replace('_', ' ', $internship->application_status)) }}</div>
                <div><span class="fw-semibold">Student Access:</span> {{ $internship->student_access ? 'True' : 'False' }}</div>
                <div><span class="fw-semibold">Submitted At:</span> {{ $internship->submitted_at ? \Illuminate\Support\Carbon::parse($internship->submitted_at)->format('Y-m-d') : '—' }}</div>
                <div><span class="fw-semibold">Notes:</span> {{ $internship->application_notes ?? '—' }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Internship Information</div>
            <div class="card-body d-flex flex-column gap-2">
                <div><span class="fw-semibold">Start Date:</span> {{ $internship->start_date ?? '—' }}</div>
                <div><span class="fw-semibold">End Date:</span> {{ $internship->end_date ?? '—' }}</div>
                <div><span class="fw-semibold">Status:</span> {{ ucwords(str_replace('_', ' ', $internship->internship_status)) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="/internships" class="btn btn-outline-secondary">Back</a>
    @if(!$isStudent)
        <a href="/internships/{{ $internship->id }}/update" class="btn btn-warning">Update</a>
        <form action="/internships/{{ $internship->id }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this internship?');">Delete</button>
        </form>
    @endif
</div>
@endsection
