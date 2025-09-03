<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private const DEPARTMENTS = ['Engineering', 'Business', 'Design'];

    public function signup(Request $request)
    {
        $step = session('register.step', 1);
        $data = session('register.data', []);
        $extra = session('register.extra', []);

        if ($request->isMethod('post')) {
            if ($step === 1) {
                $validated = $request->validate([
                    'name' => 'required|string',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:8',
                    'phone' => 'required|numeric',
                    'role' => 'required|in:student,supervisor',
                ]);

                session([
                    'register.step' => 2,
                    'register.data' => $validated,
                ]);

                return redirect()->route('signup');
            }

            $data = session('register.data');
            if (!$data) {
                return redirect()->route('signup');
            }

            if ($request->has('back')) {
                if ($data['role'] === 'student') {
                    $extraInput = $request->only(['student_number', 'national_sn', 'major', 'batch', 'photo']);
                } else {
                    $extraInput = $request->only(['supervisor_number', 'department']);
                    if ($request->hasFile('photo')) {
                        $extraInput['photo'] = $request->file('photo')->store('photos', 'public');
                    } else {
                        $extraInput['photo'] = $extra['photo'] ?? null;
                    }
                }
                session(['register.step' => 1, 'register.data' => $data, 'register.extra' => $extraInput]);
                return redirect()->route('signup');
            }

            if ($data['role'] === 'student') {
                $validated = $request->validate([
                    'student_number' => 'required|numeric',
                    'national_sn' => 'required|numeric',
                    'major' => 'required|string',
                    'batch' => 'required|date_format:Y',
                    'photo' => 'nullable|string',
                ]);
            } else {
                $photoRule = empty($extra['photo']) ? 'required|image|mimes:jpeg,jpg,png,webp|max:2048' : 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048';
                $validated = $request->validate([
                    'supervisor_number' => 'required|string|max:64|regex:/^[A-Za-z0-9_-]+$/',
                    'department' => 'required|in:' . implode(',', self::DEPARTMENTS),
                    'photo' => $photoRule,
                ]);
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'],
                'role' => $data['role'],
            ]);

            if ($data['role'] === 'student') {
                Student::create([
                    'user_id' => $user->id,
                    'student_number' => $validated['student_number'],
                    'national_sn' => $validated['national_sn'],
                    'major' => $validated['major'],
                    'batch' => $validated['batch'],
                    'photo' => $validated['photo'] ?? null,
                ]);
            } else {
                $photoPath = $request->hasFile('photo') ? $request->file('photo')->store('photos', 'public') : ($extra['photo'] ?? null);
                Supervisor::create([
                    'user_id' => $user->id,
                    'supervisor_number' => $validated['supervisor_number'],
                    'department' => $validated['department'],
                    'photo' => $photoPath,
                ]);
            }

            session()->forget('register');
            session([
                'user_id' => $user->id,
                'role' => $user->role,
            ]);

            return redirect('/');
        }

        return view('auth.register', ['step' => $step, 'data' => $data, 'extra' => $extra, 'departments' => self::DEPARTMENTS]);
    }

    public function login(Request $request)
    {
        if ($request->isMethod('post')) {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $user = User::where('email', $credentials['email'])->first();

            if ($user && Hash::check($credentials['password'], $user->password)) {
                session([
                    'user_id' => $user->id,
                    'role' => $user->role,
                ]);
                $request->session()->regenerate();
                return redirect('/');
            }

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        return view('auth.login');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
