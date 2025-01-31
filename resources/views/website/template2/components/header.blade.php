<div class="flat-header flat-header-style2">
    <div class="top-bar clearfix">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-8 col-sm-12 col-xs-12">
                    <ul class="information">
                        <li class="phone lt-sp003">
                            <i class="fa fa-phone" aria-hidden="true"></i>  {{ $institution->phone ?? '0700000' }}
                        </li>
                        <li class="email">
                            <i class="fa fa-envelope" aria-hidden="true"></i> {{ $institution->email ?? 'email' }}
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
    <header class="header menu-bar hv-menu-type2">
        <div class="container">
            <div class="menu-bar-wrap clearfix">
                <div id="logo" class="logo">
                    <a href="/"><img src="images/logo/02.png" alt="images"></a>
                </div>
                <div class="mobile-button"><span></span></div>
                <div class="header-menu">
                    <nav id="main-nav" class="main-nav">
                        <ul class="menu">
                            <li><a href="/">Home</a>
                            </li>
                            <li><a href="/services">Services</a>
                            </li>
                            <li><a href="/about">About Us</a>
                            </li>
                            <li><a href="/blog">Blog</a></li>
                            <li class="nav-sing">
                                <a class="sing-in" href="/login">Sing In</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>
</div>
