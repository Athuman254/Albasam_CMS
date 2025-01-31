<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SHARIFF NASSIR GIRLS SECONDARY SCHOOL</title>

    <!-- Mobile Specific Metas-->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Bootstrap-->
    <link rel="stylesheet" href="stylesheet/bootstrap.css">

    <!-- Template Style-->
    <link rel="stylesheet" href="stylesheet/font-awesome.css">
    <link rel="stylesheet" href="stylesheet/animate.css">
    <link rel="stylesheet" href="stylesheet/style.css">
    <link rel="stylesheet" href="stylesheet/shortcodes.css">
    <link rel="stylesheet" href="stylesheet/jquery-fancybox.css">
    <link rel="stylesheet" href="stylesheet/responsive.css">
    <link rel="stylesheet" href="stylesheet/flexslider.css">
    <link rel="stylesheet" href="stylesheet/owl.theme.default.min.css">
    <link rel="stylesheet" href="stylesheet/owl.carousel.min.css">
    <link rel="stylesheet" href="stylesheet/jquery.mCustomScrollbar.min.css">

    <link href="icon/favicon.ico" rel="shortcut icon">
</head>
<body>
<div id="loading-overlay">
    <div class="loader"></div>
</div>
<div class="wrap-header">
    @include('website.template3.components.header')<!-- header -->
</div><!-- wrap-header -->
<section class="transparent-head transparent-head-style5">
    <div class="container">
        @if (count($home_sliders) == 0)
        <div class="wrap-transparent">
            <div class="pd-lf">
                <div class="title">
                    Education is the most powerfull weapon
                </div>
                <p class="text">
                    A bank is a financial institution accepts deposits from public and creates credit. For all of your needs.
                </p>
                <div class="btn-edu">
                    <ul>
                        <li class="bt-copy">
                            <a href="#" class="bg-cl667eea">Read More</a>
                        </li>
                        <li class="bt-get">
                            <a href="#" class="bg-clfff7ec">Get Started</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="pd-rg">
                <div class="videobox">
                    <a class="fancybox" data-type="iframe" href="https://www.youtube.com/embed/2Ge1GGitzLw?autoplay=1">
                        <img src="images/home3/2.png" alt="images">
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="wrap-transparent">
            <div class="pd-lf">
                <div class="title">
                   {{ $home_sliders[0]->caption_title }}
                </div>
                <p class="text">
                    {{ $home_sliders[0]->caption }}
                </p>
                <div class="btn-edu">
                    <ul>
                        <li class="bt-copy">
                            <a href="#" class="bg-cl667eea">Read More</a>
                        </li>
                        <li class="bt-get">
                            <a href="#" class="bg-clfff7ec">Get Started</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="pd-rg">
                <div class="videobox">
                    <a class="fancybox" data-type="iframe" href="https://www.youtube.com/embed/{{ $about->video_src ?? '' }}?autoplay=1">
                        <img src="{{ 'storage/'.$home_sliders[0]->image_src ?? '/images/slider/back.jpg' }}" alt="images">
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</section><!-- transparent-head -->
{{-- <section class="partner-clients partner-clients-style5">
    <div class="container">
        <div class="row">
            <div class="slide-client owl-carousel" data-auto="true" data-item="4" data-nav="false" data-dots="false">
                <ul>
                    <li><img src="images/home1/01.png" alt="images"></li>
                </ul>
                <ul>
                    <li><img src="images/home1/02.png" alt="images"></li>
                </ul>
                <ul>
                    <li><img src="images/home1/03.png" alt="images"></li>
                </ul>
                <ul>
                    <li><img src="images/home1/04.png" alt="images"></li>
                </ul>
            </div>
        </div>
    </div>
</section><!-- partner-clients --> --}}

<section class="online-courses online-courses-style5">
    <div class="container">
        <div class="title-section text-center">
            <p class="sub-title lt-sp17">Our Service and Facilities</p>
            <div class="flat-title medium">

            </div>
        </div>
        <div class="online-courses-wrap">
            <div class="flat-carousel-box data-effect clearfix" data-gap="30" data-column="3" data-column2="2" data-column3="1" data-column4="1" data-dots="false" data-auto="false" data-nav="true">
                <div class="owl-carousel">
                    @foreach ($services as $service)
                    <div class="imagebox-courses-type1">
                        <div class="featured-post">
                            <img src="{{ $service->title }}" alt="images">
                        </div>
                        <div class="author-info">
                            <div class="avatar">
                                <img src="images/home1/11.png" alt="images">
                            </div>
                            <div class="category">
                                design
                            </div>
                            <div class="name">
                                <a href="/services/{{ $service->slug }}">{{ $service->title }}</a>
                            </div>
                            <div class="border-bt">

                            </div>
                            <div class="evaluate">
                                @if ($service->price)
                                <div class="price">
                                    <span class="price-now">Ksh{{ $service->price }}</span>
                                </div>
                                @endif


                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section><!-- online-courses -->
<section class="flat-benefit flat-benefit-style5 clearfix">
    <div class="container-fluid">
        <div class="col-benefit-left">
            <div class="wrap-inconbox-benefit">
                <div class="title-section">
                    <div class="flat-title medium heading-type2">Why choose us?</div>
                </div>
                <div class="iconbox-benefit iconbox-benefit-style5">
                    <div class="row">
                        @foreach ($why_choose_us as $reason)
                        <div class="col-lg-6 col-md-6 col-sm-6 col-sx-12">
                            <div class="themesflat-content-box" data-padding="0% 4% 0% 0%" data-sdesktoppadding="0% 0% 0% 0%" data-ssdesktoppadding="0% 0% 0% 0%"data-mobipadding="0% 0% 0% 0%" data-smobipadding="0% 0% 0% 0%">
                                <div class="iconbox">
                                    <div class="iconbox-icon">
                                        <img src="{{ '/storage/'.$reason->image_src ?? 'images/home1/18.png' }}" alt="images">
                                    </div>
                                    <div class="iconbox-content img-one">
                                        <h3>
                                            <a href="#">{{ $reason->title }}</a>
                                        </h3>
                                        <p>
                                            {{ $reason->description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-benefit-right">
            <div class="apply-admission bg-apply-type1">
                <div style="background-color: {{ $web_setting->color }}"  class="apply-admission-wrap type5 bd-type2">
                    <div class="apply-admission-inner">
                        <h2 class="title text-center">
                            <span>Apply for admission</span>
                        </h2>
                    </div>
                </div>
                <div class="form-apply">
                    <div class="section-overlay333"></div>
                    <form method="POST" action="/request-admission" class="apply-now">
                        <ul>
                            <li><input type="search" placeholder="Name"></li>
                            <li><input type="search" placeholder="Email"></li>
                            <li><input type="search" placeholder="Phone"></li>
                        </ul>
                        <div class="btn-50 hv-border text-center">
                            <button style="background-color: {{ $web_setting->color }}" class="btn bg-clf0c41b">
                                Apply now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section><!-- flat-benefit -->
<section class="flat-introduce flat-introduce-style5 clearfix">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="videobox">
                    <a style="background-color: {{ $web_setting->color }}" class="fancybox" data-type="iframe" href="https://www.youtube.com/embed/{{ $about->video_src ?? '' }}?autoplay=1"></a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="content-introduce content-introduce-style5">
                    <div class="title-section">
                        <p class="sub-title lt-sp18">{{ $about->subtitle ?? 'About Us' }}</p>
                        <div class="flat-title larger heading-type1">{{ $about->title ?? 'Welcome to Our Website' }}</div>
                    </div>
                    <div class="content-introduce-inner">
                        <p>
                            @php
                            $texts = explode("|", $about->description ?? 'Education is the most powerful weapon which you can use to change the world.” Education is the key to elimi-nating gender inequality, to reducing poverty. | to creating a sustainable planet, to preventing needless deaths and illness, and to fostering peace.  ');
                        @endphp
                        {{-- {{ $about->description}} --}}
                        {{ $texts[0] ?? "" }}
                        </p>
                        <p>
                            {{ $texts[1] ?? '' }}
                        </p>
                        <div class="content-list">
                            <ul>
                                <li>
                                        <span class="text">
                                            {{ $about->stmt1 ?? 'Education is extremely important because you overcome superstitions'  }}
                                        </span>
                                </li>
                                <li>
                                        <span class="text">
                                            {{ $about->stmt2 ?? 'Education is only valuable if children are being taught right things. ' }}
                                        </span>
                                </li>
                            </ul>
                        </div>
                        <div class="btn-read-more hv-bg333">
                            <a style="background-color: {{ $web_setting->color }}" href="/about">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><!-- flat-introduce -->
<section class="flat-hobby flat-hobby-style5">
    <div class="container">
        <div class="section-heading text-center">
            <div class="title-section">
                <div class="flat-title larger heading-type17">Which one do you prefer?</div>
            </div>
            <p class="caption">
                A crew of experienced educators helms our vast and growing library. Expertise, explore and download data  learn about education-related data and research.
            </p>
        </div>
        {{-- <div class="carousel-hobby carousel-hobby-style5">
            <div class="flat-carousel-box data-effect clearfix" data-gap="30" data-column="4" data-column2="3" data-column3="2" data-column4="1" data-dots="true" data-auto="true" data-nav="true">
                <div class="owl-carousel">
                    <div class="imagebox-hobby">
                        <div class="overlay667eea"></div>
                        <img src="images/home3/2.jpg" alt="images">
                        <a href="#" class="text-white">Graphic Design</a>
                    </div>
                    <div class="imagebox-hobby">
                        <div class="overlayf0c41b"></div>
                        <img src="images/home3/3.jpg" alt="images">
                        <a href="#" class="text-white">Web Design</a>
                    </div>
                    <div class="imagebox-hobby">
                        <div class="overlay8b46f4"></div>
                        <img src="images/home3/4.jpg" alt="images">
                        <a href="#" class="text-white">Marketing</a>
                    </div>
                    <div class="imagebox-hobby">
                        <div class="overlayff5f60-op09"></div>
                        <img src="images/home3/5.jpg" alt="images">
                        <a href="#" class="text-white">Education</a>
                    </div>

                    <div class="imagebox-hobby">
                        <div class="overlay667eea"></div>
                        <img src="images/home3/2.jpg" alt="images">
                        <a href="#" class="text-white">Graphic Design</a>
                    </div>
                    <div class="imagebox-hobby">
                        <div class="overlayf0c41b"></div>
                        <img src="images/home3/3.jpg" alt="images">
                        <a href="#" class="text-white">Web Design</a>
                    </div>
                    <div class="imagebox-hobby">
                        <div class="overlay8b46f4"></div>
                        <img src="images/home3/4.jpg" alt="images">
                        <a href="#" class="text-white">Marketing</a>
                    </div>
                    <div class="imagebox-hobby">
                        <div class="overlayff5f60-op09"></div>
                        <img src="images/home3/5.jpg" alt="images">
                        <a href="#" class="text-white">Education</a>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
</section><!-- flat-hobby -->
<section class="flat-event flat-event-bg flat-event-style5 event-bg2 equalize sm-equalize-auto clearfix">
    <div class="content-event-left">
        <div class="themesflat-content-box" data-padding="11.1% 15px 11.2% 24.5%" data-sdesktoppadding="11% 15px 11% 16.9%" data-ssdesktoppadding="10% 15px 10% 8.5%" data-mobipadding="90px 15% 100px 15%" data-smobipadding="90px 15px 100px 15px">
            <div class="wrap-event">
                <div class="title-section">
                    <div class="flat-title medium heading-type8">
                        All upcoming events
                    </div>
                </div>
                <div class="inner">
                    @foreach ($events as $event)
                    <div class="content-event">
                        <div class="entry-info clearfix">
                            <div class="entry-title">
                                <a href="#">
                                    {{ $event->name }}
                                </a>
                            </div>
                            <div class="entry-meta">
                                <ul>
                                    <li class="date clearfix">
                                        <span class="icon-event icon-icons8-planner-100"></span>
                                        <span class="detail-event"> {{ $event->date }} </span>
                                    </li>
                                    <li class="time clearfix">
                                        <span class="icon-event icon-icons8-stopwatch-100"></span>
                                        <span class="detail-event">{{ $event->start_time ?? '' }}- {{ $event->end_time }}</span>
                                    </li>
                                    <li class="location clearfix">
                                        <span class="icon-event icon-icons8-marker-100"></span>
                                        <span class="detail-event">{{ $event->venue }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="content-event-right">
        <div class="themesflat-content-box" data-padding="9.2% 29.8% 0% 14.9%" data-sdesktoppadding="9.2% 20% 0% 15%" data-ssdesktoppadding="4% 8% 25% 8%"data-mobipadding="100px 15% 100px 15%" data-smobipadding="100px 15px 100px 15px">
            <div class="caption">
                FEATURED
            </div>
            <div class="wrap-event">
                <div class="flat-counter count-time" data-day="00" data-month="00" data-year="2020" data-hour="00" data-minutes="00" data-second="00">
                    <div class="counter">
                        <ul>
                            <li class="content-counter">
                                <div class="wrap-bg">
                                    <div class="inner-bg days">
                                        <span class="numb-count numb cl-fb8122">192</span>
                                        <span class="name-count">Days</span>
                                    </div>
                                </div>
                            </li>
                            <li class="content-counter">
                                <div class="wrap-bg">
                                    <div class="inner-bg hours">
                                        <span class="numb-count numb cl-33d9b2">8</span>
                                        <span class="name-count">Hours</span>
                                    </div>
                                </div>
                            </li>
                            <li class="content-counter ">
                                <div class="wrap-bg">
                                    <div class="inner-bg seconds">
                                        <span class="numb-count numb cl-f49ac1">28</span>
                                        <span class="name-count">Min</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="featured">
                    <div class="featured-wrap">
                        <img src="images/home2/8.jpg" alt="images">
                        <div class="bg-333">
                            <div class="entry-title text-center">
                                <a href="#" class="text-white">Education Conference..</a>
                            </div>
                            <ul>
                                <li class="date clearfix">
                                    <span class="icon-event icon-icons8-planner-100"></span>
                                    <span class="detail-event">July 8, 2018</span>
                                </li>
                                <li class="time clearfix">
                                    <span class="icon-event icon-icons8-stopwatch-100"></span>
                                    <span class="detail-event">5.00pm - 7.00pm</span>
                                </li>
                                <li class="location clearfix">
                                    <span class="icon-event icon-icons8-marker-100"></span>
                                    <span class="detail-event">United States</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="flat-button hv-bg333">
                    <a style="background-color: {{ $web_setting->color }}" href="#" class="btn-become">
                        Become a Sponsor
                    </a>
                </div>
            </div>
        </div>
    </div>
</section><!-- flat-event -->
<section class="testimonial testimonial-style5 clearfix">
    <div class="container">
        <div class="col-left">
            <div class="featured-post">
                <img src="images/home5/08.png" alt="images">
                <div class="stand-behind">
                    <img src="images/home5/09.png" alt="images">
                </div>
            </div>
        </div>
        <div class="col-right">
            <div class="title-section">
                <div class="flat-title medium heading-type15">Our great customers</div>
            </div>
            <div class="client-style1 wrap-testimonial clearfix">
                <div class="flat-carousel-box data-effect clearfix" data-gap="30" data-column="1" data-column2="1" data-column3="1" data-column4="1" data-dots="true" data-auto="false" data-nav="false">
                    <div class="owl-carousel">
                        @foreach ($testimonials as $testimonial)
                        <div class="client-info">
                            <span class="icon-quote icon-icons8-get-quote-filled-100"></span>
                            <div class="speech">
                                {{ $testimonial->message }}
                            </div>
                            <div class="name">
                                {{ $testimonial->name }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><!-- testimonial -->
<section class="latest-blog cl-dots1 latest-blog-type1 latest-blog-style5">
    <div class="container">
        <div class="title-section">
            <div class="flat-title medium heading-type16">
                Latest Blog
            </div>
        </div>
        <div class="flat-carousel-box data-effect clearfix" data-gap="30" data-column="2" data-column2="2" data-column3="1" data-column4="1" data-dots="true" data-auto="false" data-nav="false">
            <div class="owl-carousel">
                @foreach ($blog_posts as $post)
                <article class="post post-style1 post-bg">
                    <div class="bg clearfix">
                        <div class="position cl-667eea lt-sp4">
                            {{ $post->category->name }}
                        </div>
                        <div class="featured-post">
                            <img src="/storage/{{ $post->temp_image}}">
                        </div>
                    </div>
                    <div class="post-content clearfix">
                        <div class="entry-info cleafix">
                            <div class="avatar">
                                <img src="images/home1/36.png" alt="images">
                            </div>
                            <div class="post-title">
                                <h5>
                                    <a href="/blog/{{ $post->id }}" class="lt-sp04">{{ $post->title }}...</a>
                                </h5>
                            </div>
                        </div>

                        <div class="post-link">
                            <a href="/blog/{{ $post->id }}">Read Now</a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </div>
</section><!-- latest-blog -->
<section class="quick-link quick-link-style5 parallax parallax2">
    <div class="section-overlay"></div>
    <div class="container">
        <div class="row">
            <div class="col-lg-7">
                <div class="wrap-link-left">
                    <div  class="caption lt-sp275">
                        {{ $quick_link->welcome_text ?? '' }}
                    </div>
                    <div style="color: {{ $web_setting->color }}" class="heading-lf lt-sp03">
                        Ready to get started?
                    </div>
                    <p class="lt-sp009">
                        {{ $quick_link->description ?? '' }}
                    </p>
                    <div class="btn-apply-link">
                        <ul>
                            <li>
                                <a style="background-color: {{ $web_setting->color }}" href="#" class="btn btn-apply bg-clfbb545">Apply now</a>
                            </li>
                            <li>
                                <a href="#" class="btn btn-request lt-sp06">Request Service</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="wrap-link-right">
                    <div class="heading-rg">
                        <span>Quick Link</span>
                    </div>
                    <ul class="info-quick-link">
                        @isset($quick_link->link1_text)
                        <li>
                            <img src="images/home1/38.png" alt="images">
                            <a href="{{ $quick_link->link1_href ?? '#' }}">{{ $quick_link->link1_text }}</a>
                        </li>
                        @endisset
                        @isset($quick_link->link2_text)
                        <li>
                            <img src="images/home1/38.png" alt="images">
                            <a href="{{ $quick_link->link2_href ?? '#' }}">{{ $quick_link->link2_text }}</a>
                        </li>
                        @endisset
                        @isset($quick_link->link3_text)
                        <li>
                            <img src="images/home1/38.png" alt="images">
                            <a href="{{ $quick_link->link3_href ?? '#' }}">{{ $quick_link->link3_text }}</a>
                        </li>
                        @endisset
                        @isset($quick_link->link4_text)
                        <li>
                            <img src="images/home1/38.png" alt="images">
                            <a href="{{ $quick_link->link4_href ?? '#' }}">{{ $quick_link->link4_text }}</a>
                        </li>
                        @endisset
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section><!-- quick-link -->
<footer id="footer" class="footer-type1">
    <div class="form-send-email">
        <div class="container">
            <form action="#" class="form-send">
                <input type="search" placeholder="Enter your email...">
                <button style="background-color: {{ $web_setting->color }}" class="btn send-button bg-clf0c41b">
                    Send
                </button>
            </form>
        </div>
    </div>
    <div id="footer-widget">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-footer">
                    <div class="logo-footer">
                        <img src="images/logo/05.png" alt="images">
                    </div>
                </div>
                <div class="col-lg-2 col-company">
                    <h3 class="widget widget-title">
                        Company
                    </h3>
                    <ul class="widget-nav-menu">
                        <li><a href="#">About Company</a></li>
                        <li><a href="#">Feature Course</a></li>
                        <li><a href="#">Careers</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-link">
                    <h3 class="widget widget-title">
                        Help Links
                    </h3>
                    <ul class="widget-nav-menu">
                        <li><a href="#">Student Support</a></li>
                        <li><a href="#">Course Policy</a></li>
                        <li><a href="#">Register  Key</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-course">
                    <h3 class="widget widget-title">
                        Course
                    </h3>
                    <ul class="widget-nav-menu">
                        <li><a href="#">Wordpres</a></li>
                        <li><a href="#">Photography</a></li>
                        <li><a href="#">Learning English</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-media">
                    <h3 class="widget widget-title">
                        Social Media
                    </h3>
                    <ul class="widget-social-media">
                        <li><a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                        <li><a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                        <li><a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                        <li><a href="#"><i class="fa fa-google-plus" aria-hidden="true"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div id="bottom" class="bottom-type1 clearfix has-spacer">
        <div id="bottom-bar-inner" class="container">
            <div class="bottom-bar-inner-wrap">
                <div class="bottom-bar-content">
                    <div id="copyright">
                        ©
                        <span class="text-year">
                                2018
                            </span>
                        <span class="text-name">
                                Roy Design.
                            </span>
                        <span class="license">
                                <a href="#">All Rights Reserved</a>
                            </span>
                    </div>
                </div>
                <div class="bottom-bar-menu">
                    <ul class="bottom-nav">
                        <li class="menu-item"><a href="#">About Company</a></li>
                        <li class="menu-item"><a href="#">Privacy Policy</a></li>
                        <li class="menu-item"><a href="#">Help Center</a></li>
                        <li class="menu-item"><a href="#">Terms</a></li>
                        <li class="menu-item"><a href="#">Site Map</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <a id="scroll-top" class="show"></a>
</footer><!-- footer -->

<script src="javascript/jquery.min.js"></script>
<script src="javascript/rev-slider.js"></script>
<script src="javascript/plugins.js"></script>
<script src="javascript/jquery-countTo.js"></script>
<script src="javascript/jquery-ui.js"></script>
<script src="javascript/jquery-fancybox.js"></script>
<script src="javascript/flex-slider.min.js"></script>
<script src="javascript/scroll-img.js"></script>
<script src="javascript/owl.carousel.min.js"></script>
<script src="javascript/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="javascript/parallax.js"></script>
<script src="javascript/jquery-isotope.js"></script>
<script src="javascript/equalize.min.js"></script>
<script src="javascript/main.js"></script>

<!-- slider -->
<script src="rev-slider/js/jquery.themepunch.tools.min.js"></script>
<script src="rev-slider/js/jquery.themepunch.revolution.min.js"></script>

<!-- Load Extensions only on Local File Systems ! The following part can be removed on Server for On Demand Loading -->
<script src="rev-slider/js/extensions/extensionsrevolution.extension.actions.min.js"></script>
<script src="rev-slider/js/extensions/extensionsrevolution.extension.carousel.min.js"></script>
<script src="rev-slider/js/extensions/extensionsrevolution.extension.kenburn.min.js"></script>
<script src="rev-slider/js/extensions/extensionsrevolution.extension.layeranimation.min.js"></script>
<script src="rev-slider/js/extensions/extensionsrevolution.extension.migration.min.js"></script>
<script src="rev-slider/js/extensions/extensionsrevolution.extension.navigation.min.js"></script>
<script src="rev-slider/js/extensions/extensionsrevolution.extension.parallax.min.js"></script>
<script src="rev-slider/js/extensions/extensionsrevolution.extension.slideanims.min.js"></script>
<script src="rev-slider/js/extensions/extensionsrevolution.extension.video.min.js"></script>
</body>
</html>
