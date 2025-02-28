<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="csrf-token" content={{ csrf_token() }}/>
    <title inertia>{{ config('app.name', 'School Management System') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon-291*301.png') }}" type="image/x-icon"/>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon"/>

    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />
    <!-- Core CSS -->

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('/assets/vendor/css/pages/page-auth.css')}}" />

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
{{--    <script src="{{ asset('assets/js/config.js') }}"></script>--}}

    <!-- Dynamic Assets -->
    @vite(['resources/css/scss/app.scss', 'resources/js/app.js', "resources/js/components/pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body>

    @inertia

    <!-- Place this tag before closing body tag for github widget button. -->
    <script async defer src="{{ asset('assets/js/buttons.js') }}"></script>
</body>
</html>
