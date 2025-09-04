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
<header class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b px-3 flex items-center">
    <button class="p-2 rounded-lg border md:hidden" id="sidebarToggle" aria-label="Toggle sidebar" aria-controls="sidebar" aria-expanded="true">
        <span class="sr-only">Toggle menu</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <div class="ml-auto relative" x-data="{open:false}">
        <button @click="open=!open" :aria-expanded="open.toString()" class="flex items-center gap-2 rounded-lg border px-3 py-2">
            @if($photo)
                <img src="{{ $photo }}" alt="Foto profil" class="rounded-full w-8 h-8 object-cover">
            @else
                <span class="inline-block w-8 h-8 rounded-full bg-gray-200"></span>
            @endif
            <span>{{ $name }}</span>
        </button>
        <ul x-show="open" @click.outside="open=false" class="absolute right-0 mt-2 w-40 rounded-lg border bg-white shadow-md py-1">
            @if($settingsUrl)
            <li><a class="block px-4 py-2 hover:bg-gray-50" href="{{ $settingsUrl }}">Pengaturan</a></li>
            <li><hr class="my-1"></li>
            @endif
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-50">Logout</button>
                </form>
            </li>
        </ul>
    </div>
</header>
