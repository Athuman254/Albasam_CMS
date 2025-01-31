<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SHARIFF NASSIR GIRLS SECONDARY SCHOOL</title>

    <!-- Mobile Specific Metas-->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Bootstrap-->
    <link rel="stylesheet" href="/stylesheet/bootstrap.css">

    <!-- Template Style-->
    <link rel="stylesheet" href="/stylesheet/font-awesome.css">
    <link rel="stylesheet" href="/stylesheet/animate.css">
    <link rel="stylesheet" href="/stylesheet/style.css">
    <link rel="stylesheet" href="/stylesheet/shortcodes.css">
    <link rel="stylesheet" href="/stylesheet/jquery-fancybox.css">
    <link rel="stylesheet" href="/stylesheet/responsive.css">
    <link rel="stylesheet" href="/stylesheet/flexslider.css">
    <link rel="stylesheet" href="/stylesheet/owl.theme.default.min.css">
    <link rel="stylesheet" href="/stylesheet/owl.carousel.min.css">
    <link rel="stylesheet" href="/stylesheet/jquery.mCustomScrollbar.min.css">

    <link href="icon/favicon.ico" rel="shortcut icon">
</head>
<body>
@include('website.shared.header')
    <div class="courses-single-page">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="content-page-wrap clearfix">
                        <div class="course-single">
                            <div class="featured-post">
                                <div class="entry-image">
                                    <div class="videobox">
                                        <a class="fancybox" data-type="iframe" href="https://www.youtube.com/embed/{{ $service->vedio_src ?? '2Ge1GGitzLw'}}?autoplay=1">
                                            <img src="{{ '/storage/'.$service->featured_image ?? '/images/course-single/1.jpg'}}" alt="images">
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="content">
                                <div class="title">
                                    <a href="#">{{ $service->title }}</a>
                                </div>
                                <p>
                                    {{ $service->discription }}
                                </p>
                                <div class="author-price">
                                    <div class="author">
                                        <div class="avatar">
                                            <img src="images/course-single/2.jpg" alt="images">
                                        </div>

                                    </div>
                                    <div class="price-wrap price-course-single">
                                        <div class="price">
                                            {{-- <span class="price-previou">
                                                <del>$169</del>
                                            </span> --}}
                                            <span class="price-now">Ksh{{ $service->price }}</span>
                                        </div>
                                        <div  style="background-color: {{ $web_setting->color }}" class="btn-buynow">
                                            <a href="#">Contact</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flat-tabs">
                            <ul class="tab-title type1 clearfix">
                                <li style="background-color: {{ $web_setting->color }}" class="item-title  overview">
                                    <span class="inner">OVERVIEW</span>
                                </li>
                                {{-- <li class="item-title curriculum">
                                    <span class="inner">CURRICULUM</span>
                                </li>
                                <li class="item-title instructor">
                                    <span class="inner">INSTRUCTOR</span>
                                </li>
                                <li class="item-title review">
                                    <span class="inner">REVIEW</span>
                                </li> --}}
                            </ul>
                            <div class="tab-content-wrap">
                                <div class="tab-content">
                                    <div class="item-content">
                                        {!! $service->content !!}
                                    </div>
                                </div>
                                <div class="tab-content">
                                    <div class="item-content">
                                        <div class="question-sg text clearfix">

                                        </div>
                                        <div class="access-sg text clearfix">
                                            <div class="title">
                                                <a href="#">Access on mobile and TV</a>
                                            </div>
                                            <p>
                                                Access  mobile deep reinforcement learning algorithms and from Deep Networks to Deep Deterministic Policy Gradients. Apply these concepts to train agents to tv walk, drive, or perform other complex tasks.
                                            </p>
                                        </div>
                                        <div class="certificate-sg text clearfix">
                                            <div class="title">
                                                <a href="#">Certificate of Completion</a>
                                            </div>
                                            <p>
                                                Access  mobile deep reinforcement learning algorithms from Deep Q Networks to Deep Deterministic Policy Gradients. Apply these concepts to train agents to tv walk, drive, or perform other complex tasks.
                                            </p>
                                            <div class="certificate">
                                                <div class="certificate-wrap">
                                                    <p>
                                                        An eduking is a blog created for educational purposes. Eduking blog archive and support student and teacher learning by facilitating reflection, questioning by self becoming a means for educators.
                                                    </p>
                                                    <ul class="list-certificate">
                                                        <li>
                                                            Graphic designers create visual concepts,
                                                        </li>
                                                        <li>
                                                            Remember skill can developed with practice.
                                                        </li>
                                                        <li>
                                                            The field is considered a subset of visual communication design.
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="images-certificate">
                                                    <img src="images/course-single/3.jpg" alt="images">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="requirements-sg text clearfix">
                                            <div class="title">
                                                <a href="#">Requirements</a>
                                            </div>
                                            <ul class="request">
                                                <li>
                                                   Understand what visual learning is for and how it is used
                                                </li>
                                                <li>
                                                   Need knowledge of photoshop and basic knowledge of indesign.
                                                </li>
                                                <li>
                                                   Preferable to have experience with PS, Sketch, Indesign and  Adobe XD.
                                                </li>
                                                <li>
                                                   Preferable to understand word embeddings
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="description-single text clearfix">
                                            <div class="title">
                                                <a href="#">Description</a>
                                            </div>
                                            <p>
                                                Your ability to use types is one of the things that differentiates graphic design from others visual professions. A big parts of graphic design is understanding typography, developing your knowledge of typefaces, & how to apply them in your design. This will be a constant study throughout your career.
                                            </p>
                                        </div>

                                        <div class="price-course-single">
                                            <div class="price">
                                                <span class="price-previou">
                                                    <del>$169</del>
                                                </span>
                                                <span class="price-now">$169</span>
                                            </div>
                                            <div class="btn-buynow">
                                                <a href="#">Buy Now</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-content">
                                    <div class="item-content">
                                        <div class="question-sg text clearfix">
                                            <div class="title">
                                                <a href="#">What will i learn?</a>
                                            </div>
                                            <p>
                                                Learn cutting edge deep reinforcement learning algorithms from Deep Q Networks (DQN) to Deep Deterministic Policy Gradients (DDPG). Apply these concepts to train agents to walk, drive, or perform other complex tasks.
                                            </p>
                                        </div>
                                        <div class="access-sg text clearfix">
                                            <div class="title">
                                                <a href="#">Access on mobile and TV</a>
                                            </div>
                                            <p>
                                                Access  mobile deep reinforcement learning algorithms and from Deep Networks to Deep Deterministic Policy Gradients. Apply these concepts to train agents to tv walk, drive, or perform other complex tasks.
                                            </p>
                                        </div>
                                        <div class="certificate-sg text clearfix">
                                            <div class="title">
                                                <a href="#">Certificate of Completion</a>
                                            </div>
                                            <p>
                                                Access  mobile deep reinforcement learning algorithms from Deep Q Networks to Deep Deterministic Policy Gradients. Apply these concepts to train agents to tv walk, drive, or perform other complex tasks.
                                            </p>
                                            <div class="certificate">
                                                <div class="certificate-wrap">
                                                    <p>
                                                        An eduking is a blog created for educational purposes. Eduking blog archive and support student and teacher learning by facilitating reflection, questioning by self becoming a means for educators.
                                                    </p>
                                                    <ul class="list-certificate">
                                                        <li>
                                                            Graphic designers create visual concepts,
                                                        </li>
                                                        <li>
                                                            Remember skill can developed with practice.
                                                        </li>
                                                        <li>
                                                            The field is considered a subset of visual communication design.
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="images-certificate">
                                                    <img src="images/course-single/3.jpg" alt="images">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="requirements-sg text clearfix">
                                            <div class="title">
                                                <a href="#">Requirements</a>
                                            </div>
                                            <ul class="request">
                                                <li>
                                                   Understand what visual learning is for and how it is used
                                                </li>
                                                <li>
                                                   Need knowledge of photoshop and basic knowledge of indesign.
                                                </li>
                                                <li>
                                                   Preferable to have experience with PS, Sketch, Indesign and  Adobe XD.
                                                </li>
                                                <li>
                                                   Preferable to understand word embeddings
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="description-single text clearfix">
                                            <div class="title">
                                                <a href="#">Description</a>
                                            </div>
                                            <p>
                                                Your ability to use types is one of the things that differentiates graphic design from others visual professions. A big parts of graphic design is understanding typography, developing your knowledge of typefaces, & how to apply them in your design. This will be a constant study throughout your career.
                                            </p>
                                        </div>

                                        <div class="price-course-single">
                                            <div class="price">
                                                <span class="price-previou">
                                                    <del>$169</del>
                                                </span>
                                                <span class="price-now">$169</span>
                                            </div>
                                            <div class="btn-buynow">
                                                <a href="#">Buy Now</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-content">
                                    <div class="item-content">
                                        <div class="question-sg text clearfix">
                                            <div class="title">
                                                <a href="#">What will i learn?</a>
                                            </div>
                                            <p>
                                                Learn cutting edge deep reinforcement learning algorithms from Deep Q Networks (DQN) to Deep Deterministic Policy Gradients (DDPG). Apply these concepts to train agents to walk, drive, or perform other complex tasks.
                                            </p>
                                        </div>
                                        <div class="access-sg text clearfix">
                                            <div class="title">
                                                <a href="#">Access on mobile and TV</a>
                                            </div>
                                            <p>
                                                Access  mobile deep reinforcement learning algorithms and from Deep Networks to Deep Deterministic Policy Gradients. Apply these concepts to train agents to tv walk, drive, or perform other complex tasks.
                                            </p>
                                        </div>
                                        <div class="certificate-sg text clearfix">
                                            <div class="title">
                                                <a href="#">Certificate of Completion</a>
                                            </div>
                                            <p>
                                                Access  mobile deep reinforcement learning algorithms from Deep Q Networks to Deep Deterministic Policy Gradients. Apply these concepts to train agents to tv walk, drive, or perform other complex tasks.
                                            </p>
                                            <div class="certificate">
                                                <div class="certificate-wrap">
                                                    <p>
                                                        An eduking is a blog created for educational purposes. Eduking blog archive and support student and teacher learning by facilitating reflection, questioning by self becoming a means for educators.
                                                    </p>
                                                    <ul class="list-certificate">
                                                        <li>
                                                            Graphic designers create visual concepts,
                                                        </li>
                                                        <li>
                                                            Remember skill can developed with practice.
                                                        </li>
                                                        <li>
                                                            The field is considered a subset of visual communication design.
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="images-certificate">
                                                    <img src="images/course-single/3.jpg" alt="images">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="requirements-sg text clearfix">
                                            <div class="title">
                                                <a href="#">Requirements</a>
                                            </div>
                                            <ul class="request">
                                                <li>
                                                   Understand what visual learning is for and how it is used
                                                </li>
                                                <li>
                                                   Need knowledge of photoshop and basic knowledge of indesign.
                                                </li>
                                                <li>
                                                   Preferable to have experience with PS, Sketch, Indesign and  Adobe XD.
                                                </li>
                                                <li>
                                                   Preferable to understand word embeddings
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="description-single text clearfix">
                                            <div class="title">
                                                <a href="#">Description</a>
                                            </div>
                                            <p>
                                                Your ability to use types is one of the things that differentiates graphic design from others visual professions. A big parts of graphic design is understanding typography, developing your knowledge of typefaces, & how to apply them in your design. This will be a constant study throughout your career.
                                            </p>
                                        </div>

                                        <div class="price-course-single">
                                            <div class="price">
                                                <span class="price-previou">
                                                    <del>$169</del>
                                                </span>
                                                <span class="price-now">$169</span>
                                            </div>
                                            <div class="btn-buynow">
                                                <a href="#">Buy Now</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="sidebar-right">
                        <div class="widget widget-class-start">
                            <div class="widget-title">
                                Service Start
                                @php
                                $timestamp = strtotime($service->starts_at);
                                $formattedDate = date('d M Y', $timestamp);
                                $formattedDate = explode(' ',$formattedDate);
                                $datearr =  explode('-',$service->starts_at);
                                // var_dump($datearr[2]);
                            @endphp
                            </div>
                            <div class="content">
                                <div class="flat-counter count-time" data-day="00" data-month="{{ $datearr[1] }}" data-year="{{ $datearr[0] }}" data-hour="00" data-minutes="00" data-second="00">
                                    <div class="counter">
                                        <ul>
                                            {{-- {{ $service->starts_at }} --}}
                                            <li class="content-counter">
                                                <div class="wrap-bg">
                                                    <div class="inner-bg days">
                                                        <div class="numb-count numb cl-667eea">178</div>
                                                        <div class="name-count">Day</div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="content-counter">
                                                <div class="wrap-bg">
                                                    <div class="inner-bg hours">
                                                        <div class="numb-count numb cl-f0c41b">12</div>
                                                        <div class="name-count">Hour</div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="content-counter">
                                                <div class="wrap-bg">
                                                    <div class="inner-bg minutes">
                                                        <div class="numb-count numb cl-8b46f4">55</div>
                                                        <div class="name-count">Minute</div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="content-counter">
                                                <div class="wrap-bg">
                                                    <div class="inner-bg seconds">
                                                        <div class="numb-count numb cl-ff5f60">55</div>
                                                        <div class="name-count">Second</div>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="widget widget-features">
                            <div class="widget-title">
                                Service Includes
                            </div>
                            <div class="content">
                                <ul class="features">
                                    @php
                                        $includes = explode(',', $service->service_includes)
                                    @endphp
                                   @foreach ($includes as $include)
                                   <li>
                                    <a href="#">{{ $include }}</a>
                                    <span></span>
                                </li>
                                   @endforeach
                                </ul>
                                <div class="share-via">
                                    <div class="title">
                                        Share via
                                    </div>
                                    <ul class="social-media">
                                        <li><a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                                        <li><a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                                        <li><a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                                        <li><a href="#"><i class="fa fa-google-plus" aria-hidden="true"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="related-course related-course-single">
                            <div class="title">
                                Related Courses
                            </div>
                            <div class="related-course-wrap client-style3">
                                <div class="flat-carousel-box data-effect clearfix" data-gap="30" data-column="1" data-column2="1" data-column3="1" data-column4="1" data-dots="false" data-auto="false" data-nav="false">
                                    <div class="owl-carousel">
                                        @foreach ($related_services as $service)
                                        <div class="flat-course">
                                            <div class="featured-post">
                                                <div class="entry-image">
                                                    <img src="{{ '/storage/'.$service->temp_image ?? '/images/course-grid/3.jpg' }}" alt="images">
                                                </div>
                                            </div>
                                            <div class="course-content clearfix">
                                                <div class="wrap-course-content">
                                                    <h4>
                                                        <a href="/services/{{ $service->slug }}">{{ $service->title }}</a>
                                                    </h4>
                                                    <p>
                                                        {{ $service->discription }}
                                                    </p>
                                                    <div class="author-info">
                                                        <div class="enroll">
                                                            <a href="#">Contact</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="wrap-rating-price">
                                                    <div class="meta-rate">

                                                        <div class="price">
                                                            <span class="price-now">{{ $service->price }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- courses-single -->
    <footer id="footer" class="footer-type1">
        <div class="form-send-email">
            <div class="container">
                <form action="#" class="form-send">
                    <input type="search" placeholder="Enter your email...">
                    <button class="btn send-button bg-clff5f60">
                        Send
                    </button>
                </form>
            </div>
        </div>
        @include('website.shared.homefooter');
        {{-- <div id="footer-widget">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-footer">
                       <div class="logo-footer">
                           <img src="images/logo/04.png" alt="images">
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
        </div> --}}
        <a id="scroll-top" class="show"></a>
    </footer><!-- footer -->

    <script src="/javascript/jquery.min.js"></script>
    <script src="/javascript/rev-slider.js"></script>
    <script src="/javascript/plugins.js"></script>
    <script src="/javascript/jquery-countTo.js"></script>
    <script src="/javascript/jquery-ui.js"></script>
    <script src="/javascript/jquery-fancybox.js"></script>
    <script src="/javascript/flex-slider.min.js"></script>
    <script src="/javascript/scroll-img.js"></script>
    <script src="/javascript/owl.carousel.min.js"></script>
    <script src="/javascript/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="/javascript/parallax.js"></script>
    <script src="/javascript/jquery-isotope.js"></script>
    <script src="/javascript/equalize.min.js"></script>
    <script src="/javascript/main.js"></script>

    <!-- slider -->
    <script src="/rev-slider/js/jquery.themepunch.tools.min.js"></script>
    <script src="/rev-slider/js/jquery.themepunch.revolution.min.js"></script>

    <!-- Load Extensions only on Local File Systems ! The following part can be removed on Server for On Demand Loading -->
    <script src="/rev-slider/js/extensions/extensionsrevolution.extension.actions.min.js"></script>
    <script src="/rev-slider/js/extensions/extensionsrevolution.extension.carousel.min.js"></script>
    <script src="/rev-slider/js/extensions/extensionsrevolution.extension.kenburn.min.js"></script>
    <script src="/rev-slider/js/extensions/extensionsrevolution.extension.layeranimation.min.js"></script>
    <script src="/rev-slider/js/extensions/extensionsrevolution.extension.migration.min.js"></script>
    <script src="/rev-slider/js/extensions/extensionsrevolution.extension.navigation.min.js"></script>
    <script src="/rev-slider/js/extensions/extensionsrevolution.extension.parallax.min.js"></script>
    <script src="/rev-slider/js/extensions/extensionsrevolution.extension.slideanims.min.js"></script>
    <script src="/rev-slider/js/extensions/extensionsrevolution.extension.video.min.js"></script>
</body>
</html>
