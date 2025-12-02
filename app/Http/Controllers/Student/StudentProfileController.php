<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentProfileController extends Controller
{
    /**
     * Show the form for editing the profile.
     */
    public function edit()
    {
        $student = Auth::guard('student')->user();
        $student->load(['rank', 'admission']);

        return Inertia::render('Student/Profile/Edit', [
            'student' => [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'admission_number' => $student->admission_number,
                'class' => $student->rank->name ?? 'N/A',
                'date_of_birth' => $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : null,
                'gender' => $student->gender->name ?? 'N/A',
                'permanent_address' => $student->permanent_address,
                'hobby' => $student->hobby,
                'medical_details' => $student->medical_details,
                'photo_url' => $student->photo_url,
            ]
        ]);
    }

    /**
     * Update the profile.
     */
    public function update(Request $request)
    {
        $student = Auth::guard('student')->user();

        $validated = $request->validate([
            'permanent_address' => 'nullable|string|max:255',
            'hobby' => 'nullable|string|max:255',
            'medical_details' => 'nullable|string|max:1000',
        ]);

        $student->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the student's password.
     */
    public function updatePassword(Request $request)
    {
        $student = Auth::guard('student')->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:student'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $student->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'force_password_change' => false,
            'password_changed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }
}
