<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LoginController extends Controller
{
    /**
     * Display the unified login form
     */
    public function index(Request $request)
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle unified login for all user types
     * Supports: Students, Admin, Teachers, Staff, Gate Staff
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');

        // 1. Try to authenticate as STUDENT
        $student = Student::where('username', $credentials['username'])->first();

        if ($student && Hash::check($credentials['password'], $student->password)) {
            Auth::guard('student')->login($student, $request->filled('remember'));
            $request->session()->regenerate();

            // Allow students to login without forced password change
            return redirect()->intended(route('student.dashboard'));
        }

        // 2. Try to authenticate as USER (Admin/Teacher/Staff)
        $user = \App\Models\User::where('email', $credentials['username'])
            ->orWhere('name', $credentials['username'])
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::guard('web')->login($user, $request->filled('remember'));
            $request->session()->regenerate();

            // If user is also an employee, log them in as employee too
            $employee = \App\Models\Employee::where('user_id', $user->id)->first();
            if ($employee) {
                Auth::guard('employee')->login($employee);
            }

            return $this->redirectBasedOnRole($user);
        }

        // 3. Try to authenticate as EMPLOYEE (if not a User)
        $employee = \App\Models\Employee::where('staff_number', $credentials['username'])
            ->orWhere('email', $credentials['username'])
            ->orWhere('primary_phone', $credentials['username'])
            ->first();

        if ($employee && Hash::check($credentials['password'], $employee->password)) {
            // Check system access
            if (!$employee->has_system_access) {
                throw ValidationException::withMessages([
                    'username' => __('Your system access has been revoked.'),
                ]);
            }

            Auth::guard('employee')->login($employee, $request->filled('remember'));

            // If employee has a linked user account, log them in as user too
            // This is crucial for Admin/Staff roles to work correctly
            if ($employee->user_id) {
                Auth::guard('web')->loginUsingId($employee->user_id, $request->filled('remember'));
                $user = Auth::guard('web')->user();

                // Regenerate session after double login
                $request->session()->regenerate();

                // Redirect based on User roles (Admin, etc)
                return $this->redirectBasedOnRole($user);
            }

            $request->session()->regenerate();
            return redirect()->intended(route('employee.dashboard'));
        }

        // Authentication failed
        throw ValidationException::withMessages([
            'username' => __('These credentials do not match our records.'),
        ]);
    }

    /**
     * Redirect user to appropriate dashboard based on their role
     */
    protected function redirectBasedOnRole($user): RedirectResponse
    {
        // Check user roles and redirect accordingly
        // Admin and other administrative staff go to Admin Dashboard
        $adminRoles = [
            'admin',
            'super admin',
            'accountant',
            'academic-coordinator',
            'hr-manager',
            'receptionist',
            'librarian',
            'principal'
        ];

        if ($user->hasRole($adminRoles)) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('teacher')) {
            return redirect()->route('employee.dashboard');
        }

        if ($user->hasRole('gate staff')) {
            return redirect()->route('gate.dashboard');
        }

        // Default to staff/employee dashboard
        return redirect()->route('employee.dashboard');
    }

    /**
     * Destroy an authenticated session (Unified logout)
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Logout from all guards
        Auth::guard('web')->logout();
        Auth::guard('student')->logout();
        Auth::guard('employee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
