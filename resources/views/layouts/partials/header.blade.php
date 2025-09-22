@php
    $userId = session('user_id');
    $role = session('role');
    $user = \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->select('name')->first();
    $name = $user->name ?? '';
    $photo = null;
    $settingsUrl = null;
    if ($role === 'student') {
        $student = \Illuminate\Support\Facades\DB::table('students')->where('user_id', $userId)->select('id','photo')->first();
        if ($student) {
            $photo = $student->photo;
            $settingsUrl = route('student.edit', ['id' => $student->id]);
        }
    } elseif ($role === 'supervisor') {
        $supervisor = \Illuminate\Support\Facades\DB::table('supervisors')->where('user_id', $userId)->select('id','photo')->first();
        if ($supervisor) {
            $photo = $supervisor->photo;
            $settingsUrl = route('supervisors.edit', ['id' => $supervisor->id]);
        }
    } elseif ($role === 'admin') {
        $settingsUrl = route('admins.edit', ['id' => $userId]);
    } elseif ($role === 'developer') {
        $settingsUrl = route('developers.edit', ['id' => $userId]);
    }
@endphp
<header class="navbar navbar-light bg-light border-bottom px-3 d-flex align-items-center">
    <button class="btn btn-outline-secondary me-3" id="sidebarToggle" aria-label="Toggle sidebar" aria-controls="sidebar" aria-expanded="true">
        <i class="bi bi-list"></i>
    </button>
    <div class="dropdown ms-auto">
        <button class="btn btn-light dropdown-toggle d-flex align-items-center" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            @if($photo)
                <img src="{{ $photo }}" alt="Profile photo" class="rounded-circle me-2" style="width:32px;height:32px;object-fit:cover;">
            @else
                <i class="bi bi-person-circle fs-4 me-2"></i>
            @endif
            <span>{{ $name }}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
            @if($settingsUrl)
            <li><a class="dropdown-item" href="{{ $settingsUrl }}">Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            @endif
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">Logout</button>
                </form>
            </li>
        </ul>
    </div>
</header>
