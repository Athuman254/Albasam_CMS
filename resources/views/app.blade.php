<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <title inertia>{{ $institution->name ?? config('app.name', 'Shariff Nassir Girls Secondary School') }}</title>

    <!-- Favicon -->
    @if(isset($favicon) && $favicon)
    <link rel="icon" href="{{ $favicon }}" type="image/x-icon" />
    <link rel="shortcut icon" href="{{ $favicon }}" type="image/x-icon" />
    @endif

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

    <!-- Scripts -->
    @routes
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])

    <!-- Mobile Menu Z-Index Fix -->
    <style>
        @media (max-width: 1199px) {
            .layout-menu {
                z-index: 1200 !important;
            }

            .layout-overlay {
                z-index: 1199 !important;
            }
        }
    </style>

    @inertiaHead
</head>

<body class="layout-default">
    @inertia
</body>

</html>