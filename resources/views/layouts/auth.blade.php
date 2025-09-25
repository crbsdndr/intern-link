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
<body class="auth-body">
<main class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <span class="auth-logo">IN</span>
            <div>
                <h1>Internish</h1>
                <p class="mb-0">Industry Internship Companion</p>
            </div>
        </div>
        @hasSection('subtitle')
            <p class="auth-subtitle">@yield('subtitle')</p>
        @endif
        @yield('content')
    </div>
</main>
<aside class="auth-illustration">
    <div class="auth-illustration-inner">
        <h2>Build industry-ready experience</h2>
        <p class="mb-4">Track applications, internships, and mentoring in one collaborative dashboard.</p>
        <div class="auth-stats">
            <div>
                <span class="label">Schools</span>
                <span class="value">120+</span>
            </div>
            <div>
                <span class="label">Internships</span>
                <span class="value">5K</span>
            </div>
            <div>
                <span class="label">Mentors</span>
                <span class="value">800+</span>
            </div>
        </div>
    </div>
</aside>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
