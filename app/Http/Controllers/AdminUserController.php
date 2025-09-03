<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    private const SEARCH_COLUMNS = ['name', 'email'];
    private const DISPLAY_COLUMNS = ['id', 'name', 'email', 'role', 'created_at', 'updated_at'];

    public function index(Request $request)
    {
        if (session('role') !== 'admin') {
            abort(401);
        }
        $query = User::query()->select(self::DISPLAY_COLUMNS)->where('id', session('user_id'));
        if ($q = trim($request->query('q', ''))) {
            $qLower = strtolower($q);
            $query->where(function ($sub) use ($qLower) {
                foreach (self::SEARCH_COLUMNS as $col) {
                    $sub->orWhereRaw('LOWER(' . $col . ') LIKE ?', ['%' . $qLower . '%']);
                }
            });
        }
        $admins = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        return view('admin.index', ['admins' => $admins]);
    }

    public function create()
    {
        abort(401);
    }

    public function store(Request $request)
    {
        abort(401);
    }

    public function show($id)
    {
        if (session('role') !== 'admin' || (int)$id !== (int)session('user_id')) {
            abort(401);
        }
        $admin = User::select(self::DISPLAY_COLUMNS)->findOrFail($id);
        return view('admin.show', compact('admin'));
    }

    public function edit($id)
    {
        if (session('role') !== 'admin' || (int)$id !== (int)session('user_id')) {
            abort(401);
        }
        $admin = User::findOrFail($id);
        return view('admin.edit', compact('admin'));
    }

    public function update(Request $request, $id)
    {
        if (session('role') !== 'admin' || (int)$id !== (int)session('user_id')) {
            abort(401);
        }
        $admin = User::findOrFail($id);
        if ($request->has('role')) {
            if ($request->input('role') === 'admin') {
                return back()->withErrors(['role' => 'Role cannot be set to admin.'])->withInput();
            }
            return back()->withErrors(['role' => 'Role modification is not allowed.'])->withInput();
        }
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'phone' => 'nullable|string',
            'password' => 'nullable|string',
        ]);
        $admin->name = $data['name'];
        $admin->email = $data['email'];
        $admin->phone = $data['phone'] ?? null;
        if (!empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }
        $admin->save();
        return redirect('/admin');
    }

    public function destroy($id)
    {
        if (session('role') !== 'admin' || (int)$id !== (int)session('user_id')) {
            abort(401);
        }
        if (User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['error' => 'Cannot delete the last admin account.']);
        }
        User::findOrFail($id)->delete();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    }
}
