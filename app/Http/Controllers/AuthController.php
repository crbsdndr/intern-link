<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegisterForm(Request $request)
    {
        $step = session('register.step', 1);
        $data = session('register.data', []);
        return view('auth.register', compact('step','data'));
    }

    public function handleRegister(Request $request)
    {
        $step = session('register.step', 1);

        if ($step === 1) {
            $validated = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'phone' => 'required|numeric',
                'role' => 'required|in:student,supervisor,admin,developer',
            ]);

            session(['register.step' => 2, 'register.data' => $validated]);
            return redirect()->route('register.show');
        }

        $data = session('register.data');
        if (!$data) {
            return redirect()->route('register.show');
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
            $validated = [];
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
        }

        session()->forget('register');
        return redirect()->route('login.show')->with('status', 'Registration successful.');
    }
}

