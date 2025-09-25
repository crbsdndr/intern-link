@extends('layouts.app')

@section('title', 'Developers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Developers</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="developer-search-form" class="search-form">
            <div class="input-group">
                <input type="search" name="q" id="developer-search-input" class="form-control" placeholder="Search developers" value="{{ request('q') }}" autocomplete="off">
                <button class="btn btn-primary" type="submit" id="developer-search-submit">Search</button>
            </div>
            @foreach(request()->except(['q','page']) as $param => $value)
                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
            @endforeach
        </form>
        <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#developerFilter" aria-controls="developerFilter">
            Filter
            @if(count($filters))
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
    </div>
</div>

@if(count($filters))
    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach($filters as $filter)
            @php($query = request()->except([$filter['param'], 'page']))
            @php($queryString = http_build_query(array_filter($query, fn($value) => $value !== null && $value !== '')))
            <a href="{{ url()->current() . ($queryString ? '?' . $queryString : '') }}" class="btn btn-sm btn-outline-primary">
                {{ $filter['label'] }}
            </a>
        @endforeach
    </div>
@endif

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col" class="text-nowrap">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($developers as $developer)
            <tr>
                <td>{{ $developer->name }}</td>
                <td>{{ $developer->email }}</td>
                <td>{{ $developer->phone ?? '—' }}</td>
                <td class="text-nowrap">
                    <a href="/developers/{{ $developer->id }}/read" class="btn btn-sm btn-outline-secondary">Read</a>
                    <a href="/developers/{{ $developer->id }}/update" class="btn btn-sm btn-warning">Update</a>
                    <form action="/developers/{{ $developer->id }}" method="POST" class="d-inline developer-delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">No developers found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<p class="text-muted mb-1">Total Developers: {{ $developers->total() }}</p>
<p class="text-muted">Page {{ $developers->currentPage() }} out of {{ $developers->lastPage() }}</p>

<div class="d-flex justify-content-between align-items-center mb-4">
    @if ($developers->onFirstPage())
        <span class="btn btn-outline-secondary disabled">Back</span>
    @else
        <a href="{{ $developers->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
    @endif

    @if ($developers->hasMorePages())
        <a href="{{ $developers->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
    @else
        <span class="btn btn-outline-secondary disabled">Next</span>
    @endif
</div>

@php($resetBase = request('q') ? url()->current() . '?q=' . urlencode(request('q')) : url()->current())
<div class="offcanvas offcanvas-end" tabindex="-1" id="developerFilter" aria-labelledby="developerFilterLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="developerFilterLabel">Filter Developer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="developer-filter-form" class="d-flex flex-column gap-3">
            @if(request('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif
            <div>
                <label class="form-label" for="developer-name">Name</label>
                <input type="text" class="form-control" id="developer-name" name="name" value="{{ request('name') }}">
            </div>
            <div>
                <label class="form-label" for="developer-email">Email</label>
                <input type="text" class="form-control" id="developer-email" name="email" value="{{ request('email') }}">
            </div>
            <div>
                <label class="form-label" for="developer-phone">Phone</label>
                <input type="text" class="form-control" id="developer-phone" name="phone" value="{{ request('phone') }}">
            </div>
            @php($emailVerified = request('email_verified'))
            <div>
                <span class="form-label d-block">Is Email Verified?</span>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="email_verified" id="dev-email-verified-true" value="true" {{ $emailVerified === 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="dev-email-verified-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="email_verified" id="dev-email-verified-false" value="false" {{ $emailVerified === 'false' ? 'checked' : '' }}>
                    <label class="form-check-label" for="dev-email-verified-false">False</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="email_verified" id="dev-email-verified-any" value="any" {{ !in_array($emailVerified, ['true','false'], true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="dev-email-verified-any">Any</label>
                </div>
            </div>
            <div>
                <label class="form-label" for="developer-email-verified-at">Email Verified At</label>
                <input type="date" class="form-control" id="developer-email-verified-at" name="email_verified_at" value="{{ request('email_verified_at') }}">
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary flex-fill" id="developer-filter-reset" data-reset-url="{{ $resetBase }}">Reset</button>
                <button type="submit" class="btn btn-primary flex-fill">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
const developerSearchForm = document.getElementById('developer-search-form');

function submitDeveloperSearch() {
    const formData = new FormData(developerSearchForm);
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        if (key === 'q' && value.trim() === '') {
            continue;
        }
        params.append(key, value);
    }
    params.delete('page');
    const query = params.toString();
    window.location = developerSearchForm.getAttribute('action') + (query ? '?' + query : '');
}

developerSearchForm.addEventListener('submit', event => {
    event.preventDefault();
    submitDeveloperSearch();
});

const developerFilterForm = document.getElementById('developer-filter-form');
const developerFilterReset = document.getElementById('developer-filter-reset');

developerFilterForm.addEventListener('submit', () => {
    const optionalRadios = developerFilterForm.querySelectorAll('input[type="radio"][value="any"]');
    optionalRadios.forEach(radio => {
        if (radio.checked) {
            radio.disabled = true;
        }
    });
    const dateInput = document.getElementById('developer-email-verified-at');
    if (dateInput && !dateInput.value) {
        dateInput.disabled = true;
    }
});

developerFilterReset.addEventListener('click', () => {
    window.location = developerFilterReset.dataset.resetUrl;
});

document.querySelectorAll('.developer-delete-form').forEach(form => {
    form.addEventListener('submit', event => {
        const confirmed = window.confirm('Are you sure you want to delete this developer? This action cannot be undone.');
        if (!confirmed) {
            event.preventDefault();
        }
    });
});
</script>
@endsection
