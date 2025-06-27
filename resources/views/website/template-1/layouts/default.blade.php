<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="google-site-verification" content="u7fyCOjASVh_qUiSHCOEotYDc6D7BvcnbV_S7Eoxg6c" />
      @stack('meta_tags')

      <link rel="shortcut icon" type="image/x-icon" href="{{ $favicon ?? '' }}" />

      <link rel="stylesheet" href="{{ asset('website/css/fonts.css') }}">
      <link rel="stylesheet" href="{{ asset('website/css/custom.css') }}">
      <link rel="stylesheet" href="{{ asset('website/css/bootstrap.min.css') }}">
      <link rel="stylesheet" href="{{ asset('/website/css/style.css') }}">
      <link rel="stylesheet" href="{{ asset('/website/css/icofont.min.css') }}">
      <link rel="stylesheet" href="{{ asset('/website/css/meanmenu.min.css') }}">
      <link rel="stylesheet" href="{{ asset('/website/css/owl.carousel.min.css')}}">
      <link rel="stylesheet" href="{{ asset('/website/css/owl.theme.default.min.css') }}">
      <link rel="stylesheet" href="{{ asset('/website/css/animate.css') }}">
      <link rel="stylesheet" href="{{ asset('/website/css/venobox.min.css')}}" />
      <link rel="stylesheet" href="{{ asset('/website/css/responsive.css') }}">

      <style>
         :root {
            font-family: 'Dosis', 'Roboto', sans-serif !important;
            font-feature-settings: 'liga' 1, 'calt' 1; /* fix for Chrome */
            --ecbz-primary: {{ $customisation->primary_color ?? '#25615a' }};
         }
         body {
            font-family: 'Dosis', 'Roboto', sans-serif !important;
         }
         h1, h2, h3, h4, h5, h6 {
            color: #333;
            font-family: 'Dosis', 'Roboto', sans-serif !important;
            font-weight: 700;
         }
         p {
            font-family: 'Roboto', 'Dosis', sans-serif !important;
         }
      </style>
      @stack('styles')
   </head>

   <body id="main">
      <div id="page-preloader">
         <div class="loader"></div>
         <div class="loa-shadow"></div>
      </div>

      @include('website.template-1.layouts.shared.header')

      @yield('page-content')

      @include('website.template-1.layouts.shared.footer')

      <script src="{{ asset('/website/js/jquery-2.2.4.min.js') }}"></script>
      <script src="{{ asset('/website/js/popper.min.js') }}"></script>
      <script src="{{ asset('/website/js/bootstrap.min.js') }}"></script>
      <script src="{{ asset('/website/js/jquery.meanmenu.js') }}"></script>
      <script src="{{ asset('/website/js/jquery.sticky.js') }}"></script>
      <script src="{{ asset('/website/js/owl.carousel.min.js') }}"></script>
      <script src="{{ asset('/website/js/isotope.3.0.6.min.js') }}"></script>
      <script src="{{ asset('/website/js/venobox.min.js') }}"></script>
      <script src="{{ asset('/website/js/jquery.appear.js') }}"></script>
      <script src="{{ asset('/website/js/jquery.inview.min.js') }}"></script>
      <script src="{{ asset('/website/js/scrolltopcontrol.js') }}"></script>
      <script src="{{ asset('/website/js/wow.min.js') }}"></script>
      <!-- scripts js -->
      <script src="{{ asset('/website/js/scripts.js') }}"></script>
   </body>
</html>
