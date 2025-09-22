<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    private const SEARCH_COLUMNS = ['name', 'email', 'phone'];
    private const BASE_SELECT = ['id', 'name', 'email', 'phone', 'email_verified_at', 'role', 'created_at', 'updated_at'];
    private const DISPLAY_COLUMNS = ['id', 'name', 'email', 'phone', 'email_verified_at'];

    public function index(Request $request)
    {
        if (!in_array(session('role'), ['admin', 'developer'])) {
            abort(401);
        }
        $query = User::query()->select(self::BASE_SELECT);
        if (session('role') === 'admin') {
            $query->where('id', session('user_id'));
        } else {
            $query->where('role', 'admin');
        }

        $filters = [];

        $name = trim($request->query('name', ''));
        if ($name !== '') {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%']);
            $filters[] = [
                'param' => 'name',
                'label' => 'Name: ' . $name,
            ];
        }

        $email = trim($request->query('email', ''));
        if ($email !== '') {
            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($email) . '%']);
            $filters[] = [
                'param' => 'email',
                'label' => 'Email: ' . $email,
            ];
        }

        $phone = trim($request->query('phone', ''));
        if ($phone !== '') {
            $query->whereRaw("LOWER(COALESCE(phone, '')) LIKE ?", ['%' . strtolower($phone) . '%']);
            $filters[] = [
                'param' => 'phone',
                'label' => 'Phone: ' . $phone,
            ];
        }

        $emailVerified = $request->query('email_verified');
        if (in_array($emailVerified, ['true', 'false'], true)) {
            if ($emailVerified === 'true') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
            $filters[] = [
                'param' => 'email_verified',
                'label' => 'Is Email Verified?: ' . ucfirst($emailVerified),
            ];
        }

        $verifiedDate = $request->query('email_verified_at');
        if ($verifiedDate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $verifiedDate)) {
            $query->whereDate('email_verified_at', $verifiedDate);
            $filters[] = [
                'param' => 'email_verified_at',
                'label' => 'Email Verified At: ' . $verifiedDate,
            ];
        }

        if ($q = trim($request->query('q', ''))) {
            $qLower = strtolower($q);
            $query->where(function ($sub) use ($qLower) {
                foreach (self::SEARCH_COLUMNS as $col) {
                    $sub->orWhereRaw('LOWER(COALESCE(' . $col . ", '')) LIKE ?", ['%' . $qLower . '%']);
                }
            });
        }

        $admins = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.index', [
            'admins' => $admins,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        if (session('role') !== 'developer') {
            abort(401);
        }
        return view('admin.create');
    }

    public function store(Request $request)
    {
        if (session('role') !== 'developer') {
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
            'role' => 'admin',
        ]);
        return redirect('/admins');
    }

    public function show($id)
    {
        $query = User::query()->select(self::DISPLAY_COLUMNS)->where('role', 'admin')->where('id', $id);
        if (session('role') === 'admin') {
            if ((int) $id !== (int) session('user_id')) {
                abort(401);
            }
        } elseif (session('role') === 'developer') {
            // developers can view any admin
        } else {
            abort(401);
        }
        $admin = $query->firstOrFail();
        return view('admin.show', compact('admin'));
    }

    public function edit($id)
    {
        $query = User::query()->where('role', 'admin')->where('id', $id);
        if (session('role') === 'admin') {
            if ((int) $id !== (int) session('user_id')) {
                abort(401);
            }
        } elseif (session('role') === 'developer') {
            // developers can edit any admin
        } else {
            abort(401);
        }
        $admin = $query->firstOrFail();
        return view('admin.edit', compact('admin'));
    }

    public function update(Request $request, $id)
    {
        $query = User::query()->where('role', 'admin')->where('id', $id);
        if (session('role') === 'admin') {
            if ((int) $id !== (int) session('user_id')) {
                abort(401);
            }
        } elseif (session('role') === 'developer') {
            // developers can update any admin
        } else {
            abort(401);
        }
        $admin = $query->firstOrFail();
        if ($request->has('role')) {
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
        return redirect('/admins');
    }

    public function destroy($id)
    {
        if (session('role') === 'admin') {
            if ((int)$id !== (int)session('user_id')) {
                abort(401);
            }
        } elseif (session('role') === 'developer') {
            // allow
        } else {
            abort(401);
        }
        if (User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['error' => 'Cannot delete the last admin account.']);
        }
        User::query()->where('role', 'admin')->where('id', $id)->firstOrFail()->delete();
        if (session('role') === 'admin') {
            session()->invalidate();
            session()->regenerateToken();
            return redirect('/login');
        }
        return redirect('/admins');
    }
}
