<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function index(Request $request){
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $homeRoute = null;

        $request->authenticate();

        $request->session()->regenerate();
        
        // Store selected role in session
        session(['logged_in_as' => $request->loginAs]);

        switch ($request->loginAs) {
            case 'admin':
                $homeRoute = '/admin/dashboard';
                break;
            case 'teacher':
                $homeRoute = '/teacher/dashboard';
                break;
            default:
                $homeRoute = '/parent/dashboard';
                break;
        }

        return redirect()->intended(url($homeRoute));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
