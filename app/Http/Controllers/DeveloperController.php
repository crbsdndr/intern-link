<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DeveloperController extends Controller
{
    /**
     * Columns searched and displayed. Adjust here if needed.
     */
    private const SEARCH_COLUMNS = ['name', 'email', 'phone'];

    public function index(Request $request)
    {
        $query = DB::table('users')
            ->select('id', 'name', 'email', 'phone', 'email_verified_at', 'created_at', 'updated_at')
            ->where('role', 'developer')
            ->where('id', session('user_id'));

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

        $query->orderBy('name');

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
