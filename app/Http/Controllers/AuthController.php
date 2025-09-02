<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show the registration form, handling both steps.
     */
    public function showRegister(Request $request)
    {
        $step1 = $request->session()->get('register.step1');

        return view('auth.register', ['step1' => $step1]);
    }

    /**
     * Handle user registration (both steps).
     */
    public function signup(Request $request)
    {
        if ($request->has('cancel')) {
            $request->session()->forget('register.step1');
            return redirect()->route('login');
        }

        if ($request->session()->has('register.step1')) {
            $step1 = $request->session()->get('register.step1');
            $rules = [];

            if ($step1['role'] === 'student') {
                $rules = [
                    'national_number' => 'required|string',
                    'national_student_number' => 'required|string',
                    'major' => 'required|string',
                    'batch' => 'required|numeric',
                    'photo' => 'required|image',
                ];
            } elseif ($step1['role'] === 'supervisor') {
                $rules = [
                    'supervisor_number' => 'required|string',
                    'department' => 'required|string',
                    'photo' => 'required|image',
                ];
            } else {
                return redirect()->route('register');
            }

            $data = $request->validate($rules);

            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('photos');
            }

            $user = User::create($step1);

            if ($step1['role'] === 'student') {
                Student::create([
                    'user_id' => $user->id,
                    'student_number' => $data['national_student_number'],
                    'national_sn' => $data['national_number'],
                    'major' => $data['major'],
                    'batch' => $data['batch'],
                    'photo' => $data['photo'],
                ]);
            } else {
                Supervisor::create([
                    'user_id' => $user->id,
                    'supervisor_number' => $data['supervisor_number'],
                    'department' => $data['department'],
                    'photo' => $data['photo'],
                ]);
            }

            $request->session()->forget('register.step1');

            session([
                'user_id' => $user->id,
                'role'    => $user->role,
            ]);

            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
            'role' => 'required|string',
        ]);

        $data['password'] = Hash::make($data['password']);

        if (in_array($data['role'], ['student', 'supervisor'])) {
            $request->session()->put('register.step1', $data);
            return redirect()->route('register');
        }

        $user = User::create($data);

        session([
            'user_id' => $user->id,
            'role'    => $user->role,
        ]);

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        session([
            'user_id' => $user->id,
            'role'    => $user->role,
        ]);

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Logged out');
    }
}
