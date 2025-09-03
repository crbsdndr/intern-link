<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DeveloperController extends Controller
{
    /**
     * Columns searched and displayed. Adjust here if needed.
     */
    private const SEARCH_COLUMNS = ['name', 'email', 'role'];
    private const DISPLAY_COLUMNS = ['id', 'name', 'email', 'role'];

    public function index(Request $request)
    {
        if (!in_array(session('role'), ['admin', 'developer'])) {
            abort(401);
        }

        $query = DB::table('users')
            ->select('id', 'name', 'email', 'role', 'created_at', 'updated_at')
            ->where('role', 'developer');
        if (session('role') === 'developer') {
            $query->where('id', session('user_id'));
        }

        $filters = [];

        if ($created = $request->query('created_at')) {
            if (Str::startsWith($created, 'range:')) {
                [$start, $end] = array_pad(explode(',', Str::after($created, 'range:')), 2, null);
                if ($start) {
                    $query->whereDate('created_at', '>=', $start);
                }
                if ($end) {
                    $query->whereDate('created_at', '<=', $end);
                }
                $filters['created_at'] = 'Created: ' . $start . ' - ' . $end;
            }
        }

        if ($updated = $request->query('updated_at')) {
            if (Str::startsWith($updated, 'range:')) {
                [$start, $end] = array_pad(explode(',', Str::after($updated, 'range:')), 2, null);
                if ($start) {
                    $query->whereDate('updated_at', '>=', $start);
                }
                if ($end) {
                    $query->whereDate('updated_at', '<=', $end);
                }
                $filters['updated_at'] = 'Updated: ' . $start . ' - ' . $end;
            }
        }

        if ($q = trim($request->query('q', ''))) {
            $qLower = strtolower($q);
            $query->where(function ($sub) use ($qLower) {
                foreach (self::SEARCH_COLUMNS as $col) {
                    $sub->orWhereRaw('LOWER(' . $col . ') LIKE ?', ['%' . $qLower . '%']);
                }
            });
        }

        $sort = $request->query('sort', 'created_at:desc');
        [$sortField, $sortDir] = array_pad(explode(':', $sort), 2, 'desc');
        $allowedSorts = array_merge(self::DISPLAY_COLUMNS, ['created_at', 'updated_at']);
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDir);

        $developers = $query->paginate(10)->withQueryString();

        return view('developer.index', [
            'developers' => $developers,
            'filters' => $filters,
        ]);
    }

    public function show($id)
    {
        if (!in_array(session('role'), ['admin', 'developer'])) {
            abort(401);
        }
        $developer = User::where('role', 'developer')->findOrFail($id);
        if (session('role') === 'developer' && $developer->id !== session('user_id')) {
            abort(401);
        }
        return view('developer.show', compact('developer'));
    }

    public function create()
    {
        if (session('role') !== 'admin') {
            abort(401);
        }
        return view('developer.create');
    }

    public function store(Request $request)
    {
        if (session('role') !== 'admin') {
            abort(401);
        }
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'password' => 'required|string',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'developer',
        ]);

        return redirect('/developer')->with('status', 'Developer created.');
    }

    public function edit($id)
    {
        if (!in_array(session('role'), ['admin', 'developer'])) {
            abort(401);
        }
        if (session('role') === 'developer' && (int) $id !== session('user_id')) {
            abort(401);
        }
        $developer = User::where('role', 'developer')->findOrFail($id);
        return view('developer.edit', compact('developer'));
    }

    public function update(Request $request, $id)
    {
        if (!in_array(session('role'), ['admin', 'developer'])) {
            abort(401);
        }
        if (session('role') === 'developer' && (int) $id !== session('user_id')) {
            abort(401);
        }
        $developer = User::where('role', 'developer')->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $developer->id,
            'phone' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        $developer->name = $data['name'];
        $developer->email = $data['email'];
        $developer->phone = $data['phone'] ?? null;
        if (!empty($data['password'])) {
            $developer->password = Hash::make($data['password']);
        }
        $developer->save();

        $message = session('role') === 'developer' ? 'Profil berhasil diperbarui.' : 'Developer updated.';
        return redirect('/developer')->with('status', $message);
    }

    public function destroy($id)
    {
        if (session('role') !== 'admin') {
            abort(401);
        }
        $developer = User::where('role', 'developer')->findOrFail($id);
        $developer->delete();
        return redirect('/developer')->with('status', 'Developer deleted.');
    }
}
