@extends('layouts.app')

@section('title', 'Admins')

@section('content')
@php($role = session('role'))
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Admins</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="admin-search-form" class="position-relative">
            <div class="input-group" style="min-width: 260px;">
                <input type="search" name="q" id="admin-search-input" class="form-control" placeholder="Search admins" value="{{ request('q') }}" autocomplete="off">
                <button class="btn btn-outline-secondary" type="submit" id="admin-search-submit">Search</button>
            </div>
            @foreach(request()->except(['q','page']) as $param => $value)
                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
            @endforeach
        </form>
        <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminFilter" aria-controls="adminFilter">
            Filter
            @if(!empty($filters))
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        @if($role === 'developer')
            <a class="btn btn-primary" href="/admins/create">Create Admin</a>
        @else
            <button class="btn btn-primary" disabled>Create Admin</button>
        @endif
    </div>
</div>

@if(!empty($filters))
    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach($filters as $filter)
            @php($query = request()->except([$filter['param'], 'page']))
            @php($queryString = http_build_query(array_filter($query, fn($value) => $value !== null && $value !== '')))
            <a href="{{ url()->current() . ($queryString ? '?' . $queryString : '') }}" class="btn btn-sm btn-outline-secondary">
                {{ $filter['label'] }}
            </a>
        @endforeach
    </div>
@endif

<div class="table-responsive">
    <table class="table table-bordered align-middle mb-3">
        <thead>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col" class="text-nowrap">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($admins as $admin)
            <tr>
                <td>{{ $admin->name }}</td>
                <td>{{ $admin->email }}</td>
                <td>{{ $admin->phone ?? '—' }}</td>
                <td class="text-nowrap">
                    <a href="/admins/{{ $admin->id }}/read" class="btn btn-sm btn-secondary">Read</a>
                    <a href="/admins/{{ $admin->id }}/update" class="btn btn-sm btn-warning">Update</a>
                    <form action="/admins/{{ $admin->id }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this admin?');">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">No admins found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<p class="text-muted mb-1">Total Admins: {{ $admins->total() }}</p>
<p class="text-muted">Page {{ $admins->currentPage() }} out of {{ $admins->lastPage() }}</p>

<div class="d-flex justify-content-between align-items-center mb-4">
    @if ($admins->onFirstPage())
        <span class="btn btn-outline-secondary disabled">Back</span>
    @else
        <a href="{{ $admins->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
    @endif

    @if ($admins->hasMorePages())
        <a href="{{ $admins->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
    @else
        <span class="btn btn-outline-secondary disabled">Next</span>
    @endif
</div>

@php($resetBase = request('q') ? url()->current() . '?q=' . urlencode(request('q')) : url()->current())
<div class="offcanvas offcanvas-end" tabindex="-1" id="adminFilter" aria-labelledby="adminFilterLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="adminFilterLabel">Filter Admins</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="admin-filter-form" class="d-flex flex-column gap-3">
            @if(request('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif
            <div>
                <label class="form-label" for="admin-name">Name</label>
                <input type="text" class="form-control" id="admin-name" name="name" value="{{ request('name') }}">
            </div>
            <div>
                <label class="form-label" for="admin-email">Email</label>
                <input type="text" class="form-control" id="admin-email" name="email" value="{{ request('email') }}">
            </div>
            <div>
                <label class="form-label" for="admin-phone">Phone</label>
                <input type="text" class="form-control" id="admin-phone" name="phone" value="{{ request('phone') }}">
            </div>
            @php($emailVerified = request('email_verified'))
            <div>
                <span class="form-label d-block">Is Email Verified?</span>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="email_verified" id="admin-email-verified-true" value="true" {{ $emailVerified === 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="admin-email-verified-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="email_verified" id="admin-email-verified-false" value="false" {{ $emailVerified === 'false' ? 'checked' : '' }}>
                    <label class="form-check-label" for="admin-email-verified-false">False</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="email_verified" id="admin-email-verified-any" value="any" {{ !in_array($emailVerified, ['true','false'], true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="admin-email-verified-any">Any</label>
                </div>
            </div>
            <div>
                <label class="form-label" for="admin-email-verified-at">Email Verified At</label>
                <input type="date" class="form-control" id="admin-email-verified-at" name="email_verified_at" value="{{ request('email_verified_at') }}">
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary flex-fill" id="admin-filter-reset" data-reset-url="{{ $resetBase }}">Reset</button>
                <button type="submit" class="btn btn-primary flex-fill">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
const adminSearchForm = document.getElementById('admin-search-form');

function submitAdminSearch() {
    const formData = new FormData(adminSearchForm);
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        if (key === 'q' && value.trim() === '') {
            continue;
        }
        params.append(key, value);
    }
    params.delete('page');
    const query = params.toString();
    window.location = adminSearchForm.getAttribute('action') + (query ? '?' + query : '');
}

adminSearchForm.addEventListener('submit', event => {
    event.preventDefault();
    submitAdminSearch();
});

const adminFilterForm = document.getElementById('admin-filter-form');
const adminFilterReset = document.getElementById('admin-filter-reset');

adminFilterForm.addEventListener('submit', () => {
    const optionalRadios = adminFilterForm.querySelectorAll('input[type="radio"][value="any"]');
    optionalRadios.forEach(radio => {
        if (radio.checked) {
            radio.disabled = true;
        }
    });
    const dateInput = document.getElementById('admin-email-verified-at');
    if (dateInput && !dateInput.value) {
        dateInput.disabled = true;
    }
});

adminFilterReset.addEventListener('click', () => {
    window.location = adminFilterReset.dataset.resetUrl;
});
</script>
@endsection
