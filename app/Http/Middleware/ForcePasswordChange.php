<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $student = Auth::guard('student')->user();

        if ($student && $student->force_password_change) {
            // Allow access to password change routes
            if ($request->routeIs('student.password.change.*') || $request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('student.password.change.form');
        }

        return $next($request);
    }
}
