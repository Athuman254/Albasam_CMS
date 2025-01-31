<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ThrottleAdmissionRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'admission_attempts_' . $request->ip();
        $maxAttempts = 3; // Maximum attempts allowed
        $decayMinutes = 60; // Time window in minutes

        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            // return redirect()->back()->with('message','Too many attempts. Please try again later.');
        }


        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
        // dd($next($request));
        return $next($request);
    }
}
