<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

        <title inertia>{{ $institution->name ?? config('app.name', 'Shariff Nassir Girls Secondary School') }}</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ $favicon ?? '' }}" type="image/x-icon"/>
        <link rel="shortcut icon" href="{{ $favicon ?? '' }}" type="image/x-icon"/>

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Page CSS -->
        <link type="text/css" href="{{ asset('/fonts/boxicons.scss') }}" />

        <!-- Helpers -->
        <script src="{{ asset('vendor/js/helpers.js') }}"></script>

        <!-- Scripts -->
        @routes
        @vite(['resources/scss/app.scss', 'resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    </head>
    <body>
        @inertia

        <script async defer src="{{ asset('vendor/js/menu.js') }}"></script>
        <script async defer src="{{ asset('assets/js/main.js') }}"></script>
        <script async defer src="{{ asset('assets/js/perfect-scrollbar.js') }}"></script>
        <script async defer src="{{ asset('assets/js/buttons.js') }}"></script>
    </a>
    </body>
</html>
