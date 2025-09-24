<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Internish')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@include('layouts.partials.header')
<div class="d-flex">
    <nav id="sidebar" class="bg-light border-end" style="min-width:200px;">
        <div class="list-group list-group-flush">
            <a href="/" class="list-group-item list-group-item-action">Dashboard</a>
            <a href="/students" class="list-group-item list-group-item-action">Students</a>
            <a href="/supervisor" class="list-group-item list-group-item-action">Supervisors</a>
            @if(session('role') === 'developer')
            <a href="/developer" class="list-group-item list-group-item-action">Developers</a>
            @endif
            <a href="/institution" class="list-group-item list-group-item-action">Institutions</a>
            <a href="/application" class="list-group-item list-group-item-action">Applications</a>
            <a href="/internship" class="list-group-item list-group-item-action">Internships</a>
            <a href="/monitoring" class="list-group-item list-group-item-action">Monitorings</a>
            @if(in_array(session('role'), ['admin','developer']))
            <a href="/admin" class="list-group-item list-group-item-action">Admin</a>
            @endif
    </div>
</nav>
    <main class="p-4 flex-fill">
        @if (session('status'))
            <div class="alert alert-info">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('sidebarToggle').addEventListener('click', function(){
    var sidebar = document.getElementById('sidebar');
    var expanded = this.getAttribute('aria-expanded') === 'true';
    sidebar.classList.toggle('d-none');
    this.setAttribute('aria-expanded', expanded ? 'false' : 'true');
});
</script>
@stack('scripts')
</body>
</html>
