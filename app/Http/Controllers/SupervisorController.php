<?php

namespace App\Http\Controllers;

use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SupervisorController extends Controller
{
    /**
     * Columns searched and displayed. Adjust here if needed.
     */
    private const SEARCH_COLUMNS = ['name', 'department'];
    private const DISPLAY_COLUMNS = ['id', 'name', 'department'];

    public function index(Request $request)
    {
        $query = DB::table('supervisor_details_view')
            ->select(self::DISPLAY_COLUMNS);

        $filters = [];

        if ($dept = $request->query('department~')) {
            $query->where('department', 'like', '%' . $dept . '%');
            $filters['department~'] = 'Department: ' . $dept;
        }

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

        $supervisors = $query->paginate(10)->withQueryString();

        return view('supervisor.index', [
            'supervisors' => $supervisors,
            'filters' => $filters,
        ]);
    }

    public function show($id)
    {
        $supervisor = DB::table('supervisor_details_view')->where('id', $id)->first();
        abort_if(!$supervisor, 404);
        return view('supervisor.show', compact('supervisor'));
    }

    public function create()
    {
        return view('supervisor.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'password' => 'required|string',
            'supervisor_number' => 'required|string|unique:supervisors,supervisor_number',
            'department' => 'required|string',
            'notes' => 'nullable|string',
            'photo' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'supervisor',
        ]);

        Supervisor::create([
            'user_id' => $user->id,
            'supervisor_number' => $data['supervisor_number'],
            'department' => $data['department'],
            'notes' => $data['notes'] ?? null,
            'photo' => $data['photo'] ?? null,
        ]);

        return redirect('/supervisor');
    }

    public function edit($id)
    {
        $supervisor = DB::table('supervisor_details_view')->where('id', $id)->first();
        abort_if(!$supervisor, 404);
        return view('supervisor.edit', compact('supervisor'));
    }

    public function update(Request $request, $id)
    {
        $supervisor = Supervisor::findOrFail($id);
        $user = $supervisor->user;

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'password' => 'nullable|string',
            'supervisor_number' => 'required|string|unique:supervisors,supervisor_number,' . $supervisor->id,
            'department' => 'required|string',
            'notes' => 'nullable|string',
            'photo' => 'nullable|string',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        $supervisor->update([
            'supervisor_number' => $data['supervisor_number'],
            'department' => $data['department'],
            'notes' => $data['notes'] ?? null,
            'photo' => $data['photo'] ?? null,
        ]);

        return redirect('/supervisor');
    }

    public function destroy($id)
    {
        $supervisor = Supervisor::findOrFail($id);
        $supervisor->user()->delete();
        return redirect('/supervisor');
    }
}
