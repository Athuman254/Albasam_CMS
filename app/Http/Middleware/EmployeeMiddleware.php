<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && (auth()->user()->is_employee || auth()->user()->hasRole('employee'))) {
            return $next($request);
        }
        
        return redirect('/admin/dashboard')->with('error', 'Access denied. Employee privileges required.');
    }
}