<header class="main-header">
   <!-- START TOP AREA -->
   <div class="top-area">
      <div class="auto-container">
         <div class="row">
            <div class="col-lg-4 col-md-12 col-sm-12 col-12 text-lg-left text-center">
               <div class="header-social">
                  <ul>
                     <li><a href="#"><i class="icofont-facebook"></i></a></li>
                     <li><a href="#"><i class="icofont-instagram"></i></a></li>
                     <li><a href="#"><i class="icofont-twitter"></i></a></li>
                     <li><a href="#"><i class="icofont-youtube"></i></a></li>
                  </ul>
               </div>
            </div>
            <!-- end col -->
            <div class="col-lg-8 col-md-12 col-sm-12 col-12 text-lg-right text-center">
               <div class="top-menu">
                  <ul>
                     <li><a href="#"><i class="icofont-location-pin"></i>{{ $institution->physical_address ?? '' }}</a></li>
                     <li><a href="#"><i class="icofont-phone"></i>{{ $institution->phone ?? '' }}</a></li>
                  </ul>
               </div>
            </div>
            <!-- end col -->
         </div>
      </div>
   </div>
   <!-- END TOP AREA -->

   <!-- START LOGO AREA -->
   <div class="logo-area">
      <div class="auto-container">
         <div class="row">
            <div class="col-lg-3 col-12 mx-auto text-lg-left text-center pl-0 mb-lg-0 mb-4">
               <div class="logo">
                  <a href="{{ route('homepage') }}">
                     @if($logo)
                        <img src="{{ $logo }}" alt="logo" style="width:180px; height:auto;">
                     @else
                        <div class="h3 mb-2 mb-sm-0 text-primary">
                           Website
                        </div>
                     @endif
                  </a>
               </div>
            </div>
            <!-- end col -->
            <div class="col-lg-9 col-12">
               <div class="header-info-box float-end">
                  <div class="header-info-icon">
                     <i class="icofont-envelope"></i>
                  </div>
                  <h5>Connect With Us</h5>
                  <p>{{ $institution->email ?? '' }}</p>
               </div>
               <div class="header-info-box float-end">
                  <div class="header-info-icon">
                     <i class="icofont-headphone-alt-3"></i>
                  </div>
                  <h5>Call For Inquiry</h5>
                  <p>{{ $institution->phone ?? '' }}</p>
               </div>
{{--               <div class="header-info-box">--}}
{{--                  <div class="header-info-icon">--}}
{{--                     <i class="icofont-eye-open"></i>--}}
{{--                  </div>--}}
{{--                  <h5>Open hours</h5>--}}
{{--                  <p>Mon - Fri : 07:00 - 17:00</p>--}}
{{--               </div>--}}
            </div>
            <!-- end col -->
         </div>
      </div>
   </div>
   <!-- END LOGO AREA -->

   <!-- START NAVIGATION AREA -->
   <div class="sticky-menu">
      <div class="mainmenu-area">
         <div class="auto-container">
            <div class="row">
               <div class="col-lg-9 d-none d-lg-block d-md-none">
                  <nav class="navbar navbar-expand-lg justify-content-left">
                     <ul class="navbar-nav">
{{--                        <li class="nav-item @if (\Request::is('/')) active @endif">--}}
{{--                           <a href="{{ route('homepage') }}" class="nav-link">--}}
{{--                              Home--}}
{{--                           </a>--}}
{{--                        </li>--}}
                        @foreach ($menus as $menu)
                           <li class="nav-item @if (\Request::is($menu->page->slug)) active @endif">
                              <a href="{{ route('page.show', $menu->page->slug) }}" class="nav-link">
                                 {{ $menu->page->title }}
                              </a>
                           </li>
                        @endforeach
                     </ul>
                  </nav>
               </div>
               <div class="col-lg-3 d-lg-block text-right">
                  <a href="{{ route('login.index') }}" target="blank" class="header-search h5 mb-0">
                     Portal
                  </a>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- END NAVIGATION AREA -->
</header>
