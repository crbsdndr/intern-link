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
     * Display the second registration step for role selection.
     */
    public function showStep2(Request $request)
    {
        if (! $request->session()->has('register.step1')) {
            return redirect()->route('register');
        }

        $step1 = $request->session()->get('register.step1');
        $data = $request->session()->get('register.step2', []);

        return view('auth.register-step2', ['data' => $data, 'step1' => $step1]);
    }

    /**
     * Store the role selection and related fields in session.
     */
    public function storeStep2(Request $request)
    {
        $step1 = $request->session()->get('register.step1');
        if (! $step1) {
            return redirect()->route('register');
        }

        $rules = ['role' => 'required|in:student,supervisor'];

        if ($request->input('role') === 'student') {
            $rules = array_merge($rules, [
                'national_number' => 'required|string',
                'national_student_number' => 'required|string',
                'major' => 'required|string',
                'batch' => 'required|numeric',
                'photo' => 'required|image',
            ]);
        } elseif ($request->input('role') === 'supervisor') {
            $rules = array_merge($rules, [
                'supervisor_number' => 'required|string',
                'department' => 'required|string',
                'photo' => 'required|image',
            ]);
        }

        $data = $request->validate($rules);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos');
        }

        $request->session()->put('register.step2', $data);

        $userData = array_merge($step1, ['role' => $data['role']]);
        $user = User::create($userData);

        if ($data['role'] === 'student') {
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

        $request->session()->forget(['register.step1', 'register.step2']);

        session([
            'user_id' => $user->id,
            'role'    => $user->role,
        ]);

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }
    /**
     * Handle user registration.
     */
    public function signup(Request $request)
    {
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
            return redirect()->route('register.step2');
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
