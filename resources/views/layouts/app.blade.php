<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Internish')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gray-50">
@include('layouts.partials.header')
<div class="flex">
    <nav id="sidebar" class="hidden md:block w-64 bg-white border-r">
        <div class="p-4 space-y-1">
            <a href="/" class="block rounded px-3 py-2 hover:bg-gray-100">Dashboard</a>
            <a href="/student" class="block rounded px-3 py-2 hover:bg-gray-100">Students</a>
            <a href="/supervisor" class="block rounded px-3 py-2 hover:bg-gray-100">Supervisors</a>
            @if(session('role') === 'developer')
            <a href="/developer" class="block rounded px-3 py-2 hover:bg-gray-100">Developers</a>
            @endif
            <a href="/institution" class="block rounded px-3 py-2 hover:bg-gray-100">Institutions</a>
            <a href="/application" class="block rounded px-3 py-2 hover:bg-gray-100">Applications</a>
            <a href="/internship" class="block rounded px-3 py-2 hover:bg-gray-100">Internships</a>
            <a href="/monitoring" class="block rounded px-3 py-2 hover:bg-gray-100">Monitorings</a>
            @if(in_array(session('role'), ['admin','developer']))
            <a href="/admin" class="block rounded px-3 py-2 hover:bg-gray-100">Admin</a>
            @endif
    </div>
</nav>
    <main class="p-4 flex-1">
        @if (session('status'))
            <x-alert type="info">{{ session('status') }}</x-alert>
        @endif
        @yield('content')
    </main>
</div>
<script>
document.getElementById('sidebarToggle')?.addEventListener('click', function(){
    var sidebar = document.getElementById('sidebar');
    var expanded = this.getAttribute('aria-expanded') === 'true';
    sidebar.classList.toggle('hidden');
    this.setAttribute('aria-expanded', expanded ? 'false' : 'true');
});
</script>
</body>
</html>
