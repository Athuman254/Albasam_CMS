<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

    <title inertia>{{ $institution->name ?? config('app.name', 'Shariff Nassir Girls Secondary School') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ $favicon ?? '' }}" type="image/x-icon"/>
    <link rel="shortcut icon" href="{{ $favicon ?? '' }}" type="image/x-icon"/>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <!-- Helpers -->
    <script src="{{ asset('assets/js/helpers.js') }}"></script>

    <!-- Dynamic Assets -->
    @routes
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @inertiaHead
</head>

<body>
    @inertia

    <script async defer src="{{ asset('assets/js/buttons.js') }}"></script>
</body>
</html>
