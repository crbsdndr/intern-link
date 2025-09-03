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
            $settingsUrl = route('supervisor.edit', ['id' => $supervisor->id]);
        }
    } elseif ($role === 'admin') {
        $settingsUrl = route('admin.edit', ['id' => $userId]);
    } elseif ($role === 'developer') {
        $settingsUrl = route('developer.edit', ['id' => $userId]);
    }
@endphp
<header class="flex items-center bg-gray-100 border-b px-3">
    <button class="border rounded px-2 py-1 text-gray-600 mr-3" id="sidebarToggle" aria-label="Toggle sidebar" aria-controls="sidebar" aria-expanded="true">☰</button>
    <div class="relative ml-auto">
        <button class="flex items-center px-2 py-1 border rounded" id="profileDropdown" aria-expanded="false">
            @if($photo)
                <img src="{{ $photo }}" alt="Foto profil" class="rounded-full mr-2" style="width:32px;height:32px;object-fit:cover;">
            @else
                <span class="text-2xl mr-2">👤</span>
            @endif
            <span>{{ $name }}</span>
        </button>
        <ul class="absolute right-0 mt-2 w-40 bg-white border rounded shadow-lg hidden" id="profileMenu">
            @if($settingsUrl)
            <li><a class="block px-4 py-2 hover:bg-gray-100" href="{{ $settingsUrl }}">Pengaturan</a></li>
            <li><hr class="my-1"></li>
            @endif
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-100">Logout</button>
                </form>
            </li>
        </ul>
    </div>
</header>
<script>
document.getElementById('profileDropdown').addEventListener('click', function(){
    document.getElementById('profileMenu').classList.toggle('hidden');
});
</script>
