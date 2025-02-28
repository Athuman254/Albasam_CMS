<!DOCTYPE html>
<html lang="en">

<head>
   <!--Meta Tags-->
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta name="description" content=""/>
   <meta name="keywords" content=""/>

   <!--Favicons-->
   <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.png') }}" />

   <!--Page Title-->
   <title>SHARIFF NASSIR GIRLS SECONDARY SCHOOL</title>

   <!-- Core CSS -->
   <link rel="stylesheet" href="{{ asset('website-assets/template-1/fonts.css') }}">
   <link rel="stylesheet" href="{{ asset('website-assets/template-1/custom.css') }}">
   <link rel="stylesheet" href="{{ asset('website-assets/template-1/assets/bootstrap/css/bootstrap.min.css') }}">
   <link rel="stylesheet" href="{{ asset('/website-assets/template-1/assets/css/style.css') }}">
   <link rel="stylesheet" href="{{ asset('/website-assets/template-1/assets/css/icofont.min.css') }}">
   <link rel="stylesheet" href="{{ asset('/website-assets/template-1/assets/css/meanmenu.min.css') }}">
   <link rel="stylesheet" href="{{ asset('/website-assets/template-1/assets/owlcarousel/css/owl.carousel.min.css')}}">
   <link rel="stylesheet" href="{{ asset('/website-assets/template-1/assets/owlcarousel/css/owl.theme.default.min.css') }}">
   <link rel="stylesheet" href="{{ asset('/website-assets/template-1/assets/css/animate.css') }}">
   <link rel="stylesheet" href="{{ asset('/website-assets/template-1/assets/venobox/css/venobox.min.css')}}" />
   <link rel="stylesheet" href="{{ asset('/website-assets/template-1/assets/css/responsive.css') }}">

   <style>
      /* CSS */
      :root {
         font-family: 'Roboto', 'Dosis', sans-serif !important;
         font-feature-settings: 'liga' 1, 'calt' 1; /* fix for Chrome */
      }
      body {
         font-family: 'Roboto', 'Dosis', sans-serif !important;
      }
      h1, h2, h3, h4, h5, h6 {
         color: #333;
         font-family: 'Roboto', 'Dosis', sans-serif !important;
         font-weight: 700;
      }
   </style>
</head>

<body id="main">

<!-- START HEADER SECTION -->
@include('website.template-1.layouts.shared.header')
<!-- END HEADER SECTION -->

@yield('page-content')

<!-- START FOOTER -->
@include('website.template-1.layouts.shared.footer')
<!-- END FOOTER -->

<!-- Latest jQuery -->
<script src="{{ asset('/website-assets/template-1/assets/js/jquery-2.2.4.min.js') }}"></script>
<!-- popper js -->
<script src="{{ asset('/website-assets/template-1/assets/bootstrap/js/popper.min.js') }}"></script>
<!-- Latest compiled and minified Bootstrap -->
<script src="{{ asset('/website-assets/template-1/assets/bootstrap/js/bootstrap.min.js') }}"></script>
<!-- Meanmenu Js -->
<script src="{{ asset('/website-assets/template-1/assets/js/jquery.meanmenu.js') }}"></script>
<!-- Sticky JS -->
<script src="{{ asset('/website-assets/template-1/assets/js/jquery.sticky.js') }}"></script>
<!-- owl-carousel min js  -->
<script src="{{ asset('/website-assets/template-1/assets/owlcarousel/js/owl.carousel.min.js') }}"></script>
<!-- isotope js -->
<script src="{{ asset('/website-assets/template-1/assets/js/isotope.3.0.6.min.js') }}"></script>
<!-- venobox js -->
<script src="{{ asset('/website-assets/template-1/assets/venobox/js/venobox.min.js') }}"></script>
<!-- jquery appear js  -->
<script src="{{ asset('/website-assets/template-1/assets/js/jquery.appear.js') }}"></script>
<!-- countTo js -->
<script src="{{ asset('/website-assets/template-1/assets/js/jquery.inview.min.js') }}"></script>
<!-- scrolltopcontrol js -->
<script src="{{ asset('/website-assets/template-1/assets/js/scrolltopcontrol.js') }}"></script>
<!-- WOW - Reveal Animations When You Scroll -->
<script src="{{ asset('/website-assets/template-1/assets/js/wow.min.js') }}"></script>
<!-- scripts js -->
<script src="{{ asset('/website-assets/template-1/assets/js/scripts.js') }}"></script>
</body>
</html>
