<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/webhook/mpesa-payment',
        '/webhook/*',
        '/sanctum/csrf-cookie',
        '/datatable/*', // Add this line to exclude all datatable routes
        // Add other webhook URLs here as needed
    ];
}