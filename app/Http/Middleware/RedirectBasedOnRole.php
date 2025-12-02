<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * Redirects users to appropriate dashboards based on their role/guard
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated on student guard
        if (Auth::guard('student')->check()) {
            // Students always go to student dashboard
            if (!$request->is('student/*')) {
                return redirect()->route('student.dashboard');
            }
        }

        // Check if user is authenticated on employee guard
        // BUT skip if they are also authenticated as a regular user (admin/staff)
        // This allows dual-authenticated users to access admin routes
        if (Auth::guard('employee')->check() && !Auth::guard('web')->check()) {
            // Employees (teachers/staff) go to employee dashboard
            if (!$request->is('employee/*')) {
                return redirect()->route('employee.dashboard');
            }
        }

        // Check if user is authenticated on web guard (admin/staff)
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            // If accessing root or dashboard, redirect to admin dashboard
            if ($request->is('/') || $request->is('dashboard')) {
                return redirect()->route('admin.dashboard');
            }
        }

        return $next($request);
    }
}
