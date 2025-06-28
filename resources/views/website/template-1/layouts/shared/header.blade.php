<header class="main-header">
   <!-- START TOP AREA -->
   <div class="top-area">
      <div class="auto-container">
         <div class="row">
            <div class="col-lg-4 col-md-12 col-sm-12 col-12 text-lg-left text-center">
               <div class="header-social">
                  <ul>
                     @if($institution->fb_profile)
                        <li><a href="{{ $institution->fb_profile }}"><i class="icofont-facebook"></i></a>
                        </li>
                     @endif
                     @if($institution->ig_profile)
                        <li><a href="{{ $institution->ig_profile }}"><i class="icofont-instagram"></i></a></li>
                     @endif
                     @if($institution->x_profile)
                        <li><a href="{{ $institution->x_profile }}"><i class="icofont-twitter"></i></a></li>
                     @endif
                     @if($institution->youtube_profile)
                        <li><a href="{{ $institution->youtube_profile }}"><i class="icofont-youtube"></i></a></li>
                     @endif
                  </ul>
               </div>
            </div>
            <div class="col-lg-8 col-md-12 col-sm-12 col-12 text-lg-right text-center">
               <div class="top-menu">
                  <ul>
                     <li><a href="#"><i class="icofont-location-pin"></i>{{ $institution->physical_address ?? '' }}</a></li>
                     <li><a href="#"><i class="icofont-phone"></i>{{ $institution->phone ?? '' }}</a></li>
                  </ul>
               </div>
            </div>
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
                        @foreach ($menus as $menu)
                           @php
                              $hasChildren = $menu->has_children && $menu->children->isNotEmpty();
                              $isPage = $menu->type === 'page';
                              $isCustom = $menu->type === 'custom';
                              $isActive = false;

                              // Determine current active status
                              if ($menu->type === 'page' && request()->is($menu->page->slug)) {
                                  $isActive = true;
                              } elseif ($menu->type === 'custom' && url()->current() === url($menu->url)) {
                                  $isActive = true;
                              }
                           @endphp

                           <li class="{{ $hasChildren ? 'dropdown' : '' }} {{ $isActive ? 'active' : '' }}">
                              @if (!$hasChildren)
                                 {{-- Simple link (either page or custom) --}}
                                 <a href="{{ $menu->type === 'page' ? route('page.show', $menu->page->slug) : url($menu->url) }}"
                                    class="nav-link">
                                    {{ $menu->title }}
                                 </a>
                              @else
                                 {{-- Dropdown parent --}}
                                 @if($isPage)
                                    <a href="{{ $menu->page->slug }}" class="nav-link">
                                       {{ $menu->title }}
                                    </a>
                                 @elseif($isCustom)
                                    <a href="{{ $menu->url ?? '#' }}" class="nav-link">
                                       {{ $menu->title }}
                                    </a>
                                 @else
                                    <a href="#" class="nav-link">
                                       {{ $menu->title }}
                                    </a>
                                 @endif
                                 <ul class="dropdown-menu">
                                    @if ($menu->child_type === 'pages')
                                       @foreach ($menu->children as $child)
                                          @if ($child->page)
                                             <li>
                                                <a href="{{ route('page.show', $child->page->slug) }}"
                                                   class="{{ request()->is($child->page->slug) ? 'active' : '' }}">
                                                   {{ $child->title }}
                                                </a>
                                             </li>
                                          @endif
                                       @endforeach
                                    @elseif ($menu->child_type === 'component' && $menu->component)
                                       @php
                                          $componentItems = app($menu->component)->where('active', true)->orderBy('id')->get();
                                       @endphp
                                       @foreach ($componentItems as $item)
                                          <li>
                                             <a href="{{ url($item->slug) }}"
                                                class="{{ request()->is($item->slug . '*') ? 'active' : '' }}">
                                                {{ $item->title }}
                                             </a>
                                          </li>
                                       @endforeach
                                    @endif
                                 </ul>
                              @endif
                           </li>
                        @endforeach
                     </ul>
                  </nav>
               </div>
               <div class="col-lg-3 d-lg-block text-right">
                  <a href="{{ route('login') }}" target="blank" class="header-search h5 mb-0">
                     Portal
                  </a>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- END NAVIGATION AREA -->
</header>
