<div id="loading-overlay">
    <div class="loader"></div>
</div>
<div class="bg-header">
    <div class="flat-header-blog">
        <div class="top-bar clearfix">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-8 col-sm-12 col-xs-12">
                        <ul class="information">
                            <li class="phone lt-sp003">
                                <i class="fa fa-phone" aria-hidden="true"></i> {{ $institution->phone ?? '' }}
                            </li>
                            <li class="email">
                                <i class="fa fa-envelope" aria-hidden="true"></i> {{ $institution->email ?? '' }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-6 col-md-4 col-sm-12 col-xs-12">
                        <ul class="nav-sing">
                            <li><a href="/login">Sing In</a></li>

                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <header class="header menu-bar header-blog hv-menu-type2">
            <div class="container">
                <div class="menu-bar-wrap clearfix">
                    <div id="logo" class="logo">
                        {{-- <a href="/"><img src="images/logo/02.png" alt="images"></a> --}}
                        {{ $institution->name }}
                    </div>
                    <div class="mobile-button"><span></span></div>
                    <div class="header-menu">
                        <nav id="main-nav" class="main-nav">
                            <ul class="menu">
                                <li><a href="/">Home</a></li>
                                <li><a href="/services">Services</a>
                                </li>
                                <li class="menu-item active"><a href="/about">About us</a>

                                </li>
                                <li><a class="menu-item" href="/blog">Blog</a>

                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </header>
        <div class="page-title page-title-blog">
            <div class="page-title-inner">
                <div class="breadcrumbs breadcrumbs-blog text-left">
                    <div class="container">
                        <div class="breadcrumbs-wrap">
                            <ul class="breadcrumbs-inner">
                                <li><a href="/">Home</a></li>
                                <li><a href="#">{{ $title ?? '' }}</a></li>
                            </ul>
                            <div class="title">
                                {{ $title ?? '' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!-- bg-header -->
