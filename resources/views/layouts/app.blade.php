<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Internish')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@include('layouts.partials.header')
@php
    $role = session('role');
    $currentPath = request()->path();
    $normalizedPath = $currentPath === '/' ? '/' : ltrim($currentPath, '/');
    $navItems = [
        [
            'label' => 'Dashboard',
            'href' => url('/'),
            'icon' => 'bi-speedometer2',
            'patterns' => ['/', 'dashboard'],
        ],
        [
            'label' => 'Students',
            'href' => url('/students'),
            'icon' => 'bi-mortarboard',
            'patterns' => ['students*'],
        ],
        [
            'label' => 'Supervisors',
            'href' => url('/supervisors'),
            'icon' => 'bi-people',
            'patterns' => ['supervisors*'],
        ],
        [
            'label' => 'Admins',
            'href' => url('/admins'),
            'icon' => 'bi-shield-lock',
            'patterns' => ['admins*'],
            'roles' => ['admin', 'developer'],
        ],
        [
            'label' => 'Developers',
            'href' => url('/developers'),
            'icon' => 'bi-code-slash',
            'patterns' => ['developers*'],
            'roles' => ['developer'],
        ],
        [
            'label' => 'Institutions',
            'href' => url('/institutions'),
            'icon' => 'bi-building',
            'patterns' => ['institutions*'],
        ],
        [
            'label' => 'Applications',
            'href' => url('/applications'),
            'icon' => 'bi-file-earmark-text',
            'patterns' => ['applications*'],
        ],
        [
            'label' => 'Internships',
            'href' => url('/internships'),
            'icon' => 'bi-briefcase',
            'patterns' => ['internships*'],
        ],
        [
            'label' => 'Monitorings',
            'href' => url('/monitorings'),
            'icon' => 'bi-clipboard-data',
            'patterns' => ['monitorings*'],
        ],
    ];

    $navItems = array_values(array_filter($navItems, function ($item) use ($role) {
        if (!isset($item['roles'])) {
            return true;
        }

        return in_array($role, $item['roles'], true);
    }));
@endphp
<div id="appShell" class="app-shell">
    <nav id="sidebar" class="app-sidebar" aria-label="Main navigation">
        <div class="sidebar-brand">
            <span class="sidebar-logo">IN</span>
            <span class="sidebar-label">Internish</span>
        </div>
        <div class="list-group list-group-flush">
            @foreach($navItems as $item)
                @php
                    $isActive = false;
                    foreach ($item['patterns'] as $pattern) {
                        if ($pattern === '/' && $currentPath === '/') {
                            $isActive = true;
                            break;
                        }

                        if ($pattern !== '/' && \Illuminate\Support\Str::is($pattern, $normalizedPath)) {
                            $isActive = true;
                            break;
                        }
                    }
                @endphp
                <a href="{{ $item['href'] }}" class="list-group-item list-group-item-action {{ $isActive ? 'active' : '' }}" @if($isActive) aria-current="page" @endif>
                    <span class="sidebar-icon"><i class="bi {{ $item['icon'] }}"></i></span>
                    <span class="sidebar-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
    <main id="appContent" class="app-content">
        @if (session('status'))
            <div class="alert alert-info mb-4">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</div>
<div id="appBackdrop" class="app-backdrop"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
