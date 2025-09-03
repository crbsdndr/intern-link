<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Internish')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@include('layouts.partials.header')
<div class="flex">
    <nav id="sidebar" class="bg-gray-100 border-r min-w-[200px]">
        <div class="flex flex-col">
            <a href="/" class="px-4 py-2 hover:bg-gray-200">Dashboard</a>
            <a href="/student" class="px-4 py-2 hover:bg-gray-200">Students</a>
            <a href="/supervisor" class="px-4 py-2 hover:bg-gray-200">Supervisors</a>
            @if(session('role') === 'developer')
            <a href="/developer" class="px-4 py-2 hover:bg-gray-200">Developers</a>
            @endif
            <a href="/institution" class="px-4 py-2 hover:bg-gray-200">Institutions</a>
            <a href="/application" class="px-4 py-2 hover:bg-gray-200">Applications</a>
            <a href="/internship" class="px-4 py-2 hover:bg-gray-200">Internships</a>
            <a href="/monitoring" class="px-4 py-2 hover:bg-gray-200">Monitorings</a>
            @if(in_array(session('role'), ['admin','developer']))
            <a href="/admin" class="px-4 py-2 hover:bg-gray-200">Admin</a>
            @endif
    </div>
</nav>
    <main class="p-4 flex-1">
        @if (session('status'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</div>
<script>
document.getElementById('sidebarToggle').addEventListener('click', function(){
    var sidebar = document.getElementById('sidebar');
    var expanded = this.getAttribute('aria-expanded') === 'true';
    sidebar.classList.toggle('hidden');
    this.setAttribute('aria-expanded', expanded ? 'false' : 'true');
});
</script>
</body>
</html>
