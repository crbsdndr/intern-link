<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Internish')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <nav class="bg-light border-end" style="min-width:200px;">
        <div class="list-group list-group-flush">
            <a href="/student" class="list-group-item list-group-item-action">Students</a>
            <a href="/supervisor" class="list-group-item list-group-item-action">Supervisors</a>
            <a href="/application" class="list-group-item list-group-item-action">Applications</a>
            <a href="/internship" class="list-group-item list-group-item-action">Internships</a>
            <a href="/monitor" class="list-group-item list-group-item-action">Monitors</a>
        </div>
    </nav>
    <main class="p-4 flex-fill">
        @yield('content')
    </main>
</div>
</body>
</html>
