<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user->load('roles');

        return Inertia::render('Profile/Index', [
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:50'],
            'username' => ['required', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['nullable'],
            'password' => ['nullable', 'max:30']
        ]);

        $loggedInUser = User::find(auth()->user()->id);

        if($validated['email'] === $loggedInUser->email){
            if($validated['password'] === null || $validated['password'] === ''){
                $loggedInUser->update([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'phone' => $validated['phone'],
                ]);
            }else{
                $loggedInUser->update([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'phone' => $validated['phone'],
                    'password' => Hash::make($validated['password'])
                ]);
            }
        }else{
            if($validated['password'] === null || $validated['password'] === ''){
                $loggedInUser->update([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                ]);
            }else{
                $loggedInUser->update([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'password' => Hash::make($validated['password'])
                ]);
            }
        }

        return to_route('profile.index')->with('success', 'Details updated.');
    }
}
