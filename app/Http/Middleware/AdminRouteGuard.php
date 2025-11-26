<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminRouteGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // // dd($request->user());
        // switch ($request->user()) {
        //     case 'admin':
        //         $homeroute = '/admin/dashboard';
        //         break;
        //     case 'teacher':
        //         $homeroute = '/teacher/dashboard';
        //         break;
        //     default:
        //         $homeroute = '/parent/dashboard';
        //         break;
        // }
        // if(!$request->user()->is_teacher && !$request->user()->is_parent) {
        //     return $next($request);
        // }else{
        //     return redirect()->back();
        // }

    }
}
