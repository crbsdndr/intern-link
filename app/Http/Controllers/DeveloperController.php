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
        $query = DB::table('users')
            ->select('id', 'name', 'email', 'role', 'created_at', 'updated_at')
            ->where('role', 'developer')
            ->where('id', session('user_id'));

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
        $developer = User::where('role', 'developer')->findOrFail($id);
        return view('developer.show', compact('developer'));
    }

    public function create()
    {
        abort(401, 'Akses ditolak.');
    }

    public function store(Request $request)
    {
        abort(401, 'Akses ditolak.');
    }

    public function edit($id)
    {
        $developer = User::where('role', 'developer')->findOrFail($id);
        return view('developer.edit', compact('developer'));
    }

    public function update(Request $request, $id)
    {
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

        return redirect('/developer')->with('status', 'Profil berhasil diperbarui.');
    }

    public function destroy($id)
    {
        abort(401, 'Akses ditolak.');
    }
}
