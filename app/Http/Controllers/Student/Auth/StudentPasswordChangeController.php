<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class StudentPasswordChangeController extends Controller
{
    /**
     * Show the password change form.
     */
    public function show()
    {
        return Inertia::render('Student/Auth/ChangePassword');
    }

    /**
     * Update the student's password.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:student'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $student = Auth::guard('student')->user();

        $student->update([
            'password' => Hash::make($validated['password']),
            'force_password_change' => false,
            'password_changed_at' => now(),
        ]);

        return redirect()->route('student.dashboard')->with('status', 'password-updated');
    }
}
