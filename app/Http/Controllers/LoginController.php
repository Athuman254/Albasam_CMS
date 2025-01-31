<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Providers\RouteServiceProvider;
use App\Http\Requests\LoginRequest;

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

        return redirect()->intended($homeRoute);
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
