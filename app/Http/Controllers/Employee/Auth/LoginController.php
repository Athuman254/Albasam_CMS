<?php

namespace App\Http\Controllers\Employee\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('Employee/Auth/Login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);
        
        $employee = Employee::where('has_system_access', true)
            ->where(function ($q) use ($request) {
                $q->where('staff_number', $request->identifier)
                    ->orWhere('email', $request->identifier)
                    ->orWhere('primary_phone', $request->identifier);
            })->first();
        
        if (!$employee) {
            return back()->withErrors(['identifier' => 'No matching credentials found'])->onlyInput('identifier');
        }
        if (!Hash::check($request->password, $employee->password)) {
            return back()->withErrors(['identifier' => 'Incorrect password'])->onlyInput('password');
        }
        if (!$employee && !Hash::check($request->password, $employee->password)) {
            return back()->withErrors(['identifier' => 'Invalid credentials'])->onlyInput('identifier');
        }
        
        Auth::guard('employee')->login($employee);
        
        return redirect()->intended(route('employee.dashboard'));
    }
    
    public function logout(Request $request)
    {
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('employee.login');
    }
}
