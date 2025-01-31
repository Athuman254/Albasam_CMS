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

    <style>
        :root {}
    </style>
</head>
<body>
<div id="loading-overlay">
    <div class="loader"></div>
</div>
<div class="wrap-header">
    @include('website.template2.components.header')<!-- header -->
</div><!-- wrap-header -->
<section class="flat-slider style2">
    <div class="rev_slider_wrapper fullwidthbanner-container">
        <div id="rev-slider3" class="rev_slider fullwidthabanner">
            <ul>
                <!-- Slide 1 -->
                @if (count($home_sliders) == 0)
                <li data-transition="random">
                    <!-- Main Image -->
                    <img src="images/home2/01.png" alt="" data-bgposition="center center" data-no-retina>
                    <div class="overlay"></div>

                    <!-- Layers -->
                    <div class="tp-caption tp-resizeme education"
                         data-x="['left','left','left','center']" data-hoffset="['0','4','4','15']"
                         data-y="['middle','middle','middle','middle']" data-voffset="['-161','-100','-80','-50']"
                         data-fontsize="['70','70','70','30']"
                         data-lineheight="['90','90','90','35']"
                         data-width="full"
                         data-height="none"
                         data-whitespace="normal"
                         data-transform_idle="o:1;"
                         data-transform_in="y:[100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;opacity:0;s:2000;e:Power4.easeInOut;"
                         data-transform_out="y:[100%];s:1000;e:Power2.easeInOut;s:1000;e:Power2.easeInOut;"
                         data-mask_in="x:0px;y:[100%];"
                         data-mask_out="x:inherit;y:inherit;"
                         data-start="1000"
                         data-splitin="none"
                         data-splitout="none"
                         data-responsive_offset="on"> <div class="education-text text-white">Education for all</div> </div>

                    <div class="tp-caption tp-resizeme text-white complete text-edukin"
                         data-x="['left','left','left','center']" data-hoffset="['0','4','4','15']"
                         data-y="['middle','middle','middle','middle']" data-voffset="['-70','-10','5','20']"
                         data-fontsize="['17','17','16','14']"
                         data-lineheight="['30','30','26','22']"
                         data-width="full"
                         data-height="none"
                         data-whitespace="normal"
                         data-transform_idle="o:1;"
                         data-transform_in="y:[100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;opacity:0;s:2000;e:Power4.easeInOut;"
                         data-transform_out="y:[100%];s:1000;e:Power2.easeInOut;s:1000;e:Power2.easeInOut;"
                         data-mask_in="x:0px;y:[100%];"
                         data-mask_out="x:inherit;y:inherit;"
                         data-start="1000"
                         data-splitin="none"
                         data-splitout="none"
                         data-responsive_offset="on"
                         data-paddingright="['550','155','50','2']" >Complete your educution record collection. Discover Educution's full discography. Education is not key to success in life.</div>

                    <div class="tp-caption"
                         data-x="['left','left','left','center']" data-hoffset="['0','4','4','15']"
                         data-y="['middle','middle','middle','middle']" data-voffset="['40','80','90','90']"
                         data-width="full"
                         data-height="none"
                         data-whitespace="normal"
                         data-transform_idle="o:1;"
                         data-transform_in="y:[100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;opacity:0;s:2000;e:Power4.easeInOut;"
                         data-transform_out="y:[100%];s:1000;e:Power2.easeInOut;s:1000;e:Power2.easeInOut;"
                         data-mask_in="x:0px;y:[100%];"
                         data-mask_out="x:inherit;y:inherit;"
                         data-start="1000"
                         data-splitin="none"
                         data-splitout="none"
                         data-responsive_offset="on"
                         data-paddingtop= "['50','50','50','50']"
                         data-paddingbottom= "['50','50','50','50']"> <a href="#" class="btn btn-styl1">Read More</a> </div>
                </li>
                @endif

                @foreach ( $home_sliders as $slide)
                <!-- Slide 2 -->
                <li data-transition="random">
                    <!-- Main Image -->
                    <img src="{{ 'storage/'.$slide->image_src ?? '/images/slider/back.jpg' }}" alt="" data-bgposition="center center" data-no-retina>
                    <div class="overlay"></div>

                    <!-- Layers -->

                    <div class="tp-caption tp-resizeme font-rubik education education"
                         data-x="['left','left','left','center']" data-hoffset="['0','4','4','15']"
                         data-y="['middle','middle','middle','middle']" data-voffset="['-161','-100','-80','-50']"
                         data-fontsize="['70','70','70','30']"
                         data-lineheight="['90','90','90','35']"
                         data-width="full"
                         data-height="none"
                         data-whitespace="normal"
                         data-transform_idle="o:1;"
                         data-transform_in="y:[100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;opacity:0;s:2000;e:Power4.easeInOut;"
                         data-transform_out="y:[100%];s:1000;e:Power2.easeInOut;s:1000;e:Power2.easeInOut;"
                         data-mask_in="x:0px;y:[100%];"
                         data-mask_out="x:inherit;y:inherit;"
                         data-start="1000"
                         data-splitin="none"
                         data-splitout="none"
                         data-responsive_offset="on"> <div class="education-text text-white">{{ $slide->caption_title }}</div> </div>

                    <div class="tp-caption tp-resizeme text-white complete text-edukin"
                         data-x="['left','left','left','center']" data-hoffset="['0','4','4','15']"
                         data-y="['middle','middle','middle','middle']" data-voffset="['-70','-10','5','20']"
                         data-fontsize="['17','17','16','14']"
                         data-lineheight="['30','30','26','22']"
                         data-width="full"
                         data-height="none"
                         data-whitespace="normal"
                         data-transform_idle="o:1;"
                         data-transform_in="y:[100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;opacity:0;s:2000;e:Power4.easeInOut;"
                         data-transform_out="y:[100%];s:1000;e:Power2.easeInOut;s:1000;e:Power2.easeInOut;"
                         data-mask_in="x:0px;y:[100%];"
                         data-mask_out="x:inherit;y:inherit;"
                         data-start="1000"
                         data-splitin="none"
                         data-splitout="none"
                         data-responsive_offset="on"
                         data-paddingright="['550','155','50','2']" >{{ $slide->caption }}</div>

                    <div class="tp-caption"
                         data-x="['left','left','left','center']" data-hoffset="['0','4','4','15']"
                         data-y="['middle','middle','middle','middle']" data-voffset="['40','80','90','90']"
                         data-width="full"
                         data-height="none"
                         data-whitespace="normal"
                         data-transform_idle="o:1;"
                         data-transform_in="y:[100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;opacity:0;s:2000;e:Power4.easeInOut;"
                         data-transform_out="y:[100%];s:1000;e:Power2.easeInOut;s:1000;e:Power2.easeInOut;"
                         data-mask_in="x:0px;y:[100%];"
                         data-mask_out="x:inherit;y:inherit;"
                         data-start="1000"
                         data-splitin="none"
                         data-splitout="none"
                         data-responsive_offset="on"
                         data-paddingtop= "['50','50','50','50']"
                         data-paddingbottom= "['50','50','50','50']"> <a href="#" style="background-color: {{ $web_setting->color }}" class="btn btn-styl1">Read More</a> </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div> <!-- slider -->
</section><!-- flat-slider -->
<section class="campus">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="flat-benefit-style2 clearfix">
                    @foreach ($quotes as $quote)
                    <div class="iconbox-benefit iconbox-width50">
                        <div class="iconbox iconbox-one">
                            <div class="iconbox-icon">
                                <img src="/storage/{{ $quote->image_src ?? 'images/home2/24.png' }}" alt="images">
                            </div>
                            <div class="iconbox-content">
                                <h3><a href="#">{{ $quote->title }}</a></h3>
                                <p>{{ $quote->description }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-4">
                <div class="apply-admission bg-apply-type2">
                    <div style="background-color: {{ $web_setting->color }}" class="apply-admission-wrap type1 bd-type2">
                        <div class="apply-admission-inner">
                            <h2 class="title text-center">
                                <span>Apply for admission</span>
                            </h2>
                            <div class="caption text-center">
                                Make it more simple!
                            </div>
                            <div class="apply-sent apply-sent-style1">
                                <form method="POST" action="/request-admission" class="form-sent">
                                    <input name="email" type="text" placeholder="Enter your email ....">
                                    <button class="sent-button bg-cl183251">
                                        <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="form-apply">
                        <div class="section-overlay183251"></div>
                        <form method="POST" action="/request-admission/request-admission" class="apply-now">
                            <ul>
                                <li><input type="search" placeholder="Name"></li>
                                <li><input type="search" placeholder="Email"></li>
                                <li><input type="search" placeholder="Phone"></li>
                            </ul>
                            <div class="btn-50 hv-border text-center">
                                <button style="background-color: {{ $web_setting->color }}" class="btn bg-clfbb545">
                                    Apply now
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><!-- campus -->
<section class="flat-introduce flat-introduce-style2 clearfix">
    <div class="bg"></div>
    <div class="container-fluid">
        <div class="section-about-left">
            <div class="videobox">
                <a class="fancybox" data-type="iframe" href="https://www.youtube.com/embed/{{ $about->video_src ?? '' }}?autoplay=1">
                    <img src="images/home2/9.png" alt="images">
                </a>
            </div>
        </div>
        <div class="section-about-right content-introduce">
            <div class="caption">
                {{ $about->subtitle ?? 'About Us' }}
            </div>
            <div class="title-section">
                <div class="flat-title small heading-type1">Take a tour</div>
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

                        <li>
                            <span class="text">
                                {{ $about->stmt3 ?? "Education is not key to success in life. " }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section><!-- flat-introduce -->
<section class="flat-services-style2 clearfix">
    <div class="container-fluid">
        <div class="row">
            @foreach ($services->slice(0, 4) as $service)
            <div class="col-lg-3 col-md-6">
                <div class="flat-imagebox imagebox-services themesflat-content-box" data-padding="0% 0% 0% 3.6%" data-mobipadding="0% 0% 0% 0%" data-smobipadding="0% 0% 0% 0%">
                    <div class="imagebox-content">
                        <img src="images/home2/12.png" alt="images">
                        <h5 class="text-one text-white">{{ $service->title }}</h5>
                        <p class="text-white">
                            {{ $service->discription }}
                        </p>
                        <div class="read-more">
                            <a href="{{ $service->slug }}">Read More</a>
                        </div>
                    </div>
                </div>
            </div>

            @endforeach
        </div>
    </div>
</section><!-- flat-services -->
<section class="flat-benefit style22">
    <div class="container">
        <div class="row">
            <div class="col-lg-5">
                <div class="title-section">
                    <div class="flat-title small heading-type5">
                        Why choose us?
                    </div>
                </div>
                <ul>
                    @foreach ($why_choose_us->slice(0, 3) as $reason)
                    <li>
                        <div class="iconbox-benefit">
                            <div class="iconbox">
                                <div class="iconbox-icon">
                                    <img src="{{ '/storage/'.$reason->image_src ?? 'images/home1/18.png' }}" alt="images">
                                </div>
                                <div class="iconbox-content iconbox-content-style22">
                                    <h3><a href="#">{{ $reason->title }}</a></h3>
                                    <p>
                                        {{ $reason->description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="col-lg-7">
                <div class="featured-post">
                    <img src="images/home2/2.jpg" alt="images">
                </div>
            </div>
        </div>
    </div>
</section><!-- flat-benefit -->
<section class="testimonial testimonial-style2">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 col-md-4 col-sm-12">
                <div class="featured-post">
                    <img src="images/home2/20.png" alt="images">
                </div>
            </div>
            <div class="col-lg-7 col-md-8 col-sm-12 max-width100">
                <div class="wrap-testimonial">
                    <div class="title-section">
                        <div class="flat-title small heading-type5">
                            Something Special
                        </div>
                    </div>
                    <div class="client-style2 clearfix">
                        <div class="flat-carousel-box data-effect clearfix" data-gap="30" data-column="1" data-column2="1" data-column3="1" data-column4="1" data-dots="true" data-auto="false" data-nav="false">
                            <div class="owl-carousel">
                                @foreach ($testimonials as $testimonial)
                                <div class="testimonial-content">
                                    <div class="text text-white">
                                        "{{ $testimonial->message }}
                                    </div>
                                    <div class="author-info">
                                        <h5>
                                            <a href="#">{{ $testimonial->name }}</a>
                                        </h5>
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
</section><!-- testimonial -->
<section class="latest-blog latest-blog-type2 latest-blog-style2">
    <div class="container">
        <div class="title-section">
            <div class="flat-title small heading-type7">
                Latest Blog
            </div>
        </div>
        <div class="row">
            @foreach ($blog_posts as $post)
            <div class="col-lg-4 col-md-4">
                <article class="post post-style2 box-shadow-type1">
                    <div class="featured-post">
                        <img src="/storage/{{ $post->temp_image}}" alt="images">
                    </div>
                    <div class="post-content">
                        <div class="category">
                            {{ $post->category->name }}
                        </div>
                        <div class="post-title">
                            <h5>
                                <a href="/blog/{{ $post->id }}">{!! substr($post->content,0,20)  !!}...</a>
                            </h5>
                        </div>
                        <div class="post-link">
                            <a href="/blog/{{ $post->id }}">Read Now</a>
                        </div>
                    </div>
                </article>
            </div>
            @endforeach
        </div>
    </div>
</section><!-- latest-blog -->
<section class="flat-event flat-event-bg event-bg1 flat-event-style2 clearfix">
    <div class="content-event-left">
        <div class="themesflat-content-box" data-padding="11% 15px 11.25% 24.7%" data-sdesktoppadding="11% 15px 11.25% 16.9%" data-ssdesktoppadding="10% 15px 10% 8.5%" data-mobipadding="90px 15px 60px 15%" data-smobipadding="90px 15px 60px 15px">
            <div style="background-color: {{ $web_setting->color }}" class="section-overlay"></div>
            <div class="wrap-event">
                <div class="title-section">
                    <div class="flat-title small heading-type8 text-white">
                        All upcoming events
                    </div>
                </div>
                <div class="inner">
                    @foreach ($events as $event)
                    <div class="content-event">
                        <div class="entry-info clearfix">
                            <div class="entry-title">
                                <a href="#" class="text-white">
                                    {{ $event->name }}
                                </a>
                            </div>
                            <div class="entry-meta">
                                <ul>
                                    <li class="date clearfix">
                                        <span class="icon-event icon-icons8-planner-100"></span>
                                        <span class="detail-event">{{ $event->date }}</span>
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
        {{-- <div class="partner partner-style2">
            <div class="themesflat-content-box" data-padding="0% 10% 0% 22.7%" data-sdesktoppadding="0% 10% 0% 16.9%" data-ssdesktoppadding="0% 15% 0% 8.5%" data-mobipadding="0% 15% 100px 15%" data-smobipadding="0% 15px 100px 15px">
                <div class="flat-carousel-box data-effect clearfix" data-gap="30" data-column="4" data-column2="4" data-column3="5" data-column4="3" data-dots="false" data-auto="false" data-nav="true">
                    <div class="owl-carousel">
                        <div class="imagesbox">
                            <img src="images/home2/21.png" alt="images">
                        </div>
                        <div class="imagesbox">
                            <img src="images/home2/22.png" alt="images">
                        </div>
                        <div class="imagesbox">
                            <img src="images/home2/23.png" alt="images">
                        </div>
                        <div class="imagesbox">
                            <img src="images/home2/21.png" alt="images">
                        </div>
                        <div class="imagesbox">
                            <img src="images/home2/22.png" alt="images">
                        </div>
                        <div class="imagesbox">
                            <img src="images/home2/23.png" alt="images">
                        </div>
                        <div class="imagesbox">
                            <img src="images/home2/21.png" alt="images">
                        </div>
                        <div class="imagesbox">
                            <img src="images/home2/22.png" alt="images">
                        </div>
                        <div class="imagesbox">
                            <img src="images/home2/23.png" alt="images">
                        </div>
                        <div class="imagesbox">
                            <img src="images/home2/21.png" alt="images">
                        </div>
                        <div class="imagesbox">
                            <img src="images/home2/22.png" alt="images">
                        </div>
                        <div class="imagesbox">
                            <img src="images/home2/23.png" alt="images">
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
    <div class="content-event-right">
        <div class="themesflat-content-box" data-padding="9.2% 29.8% 13.7% 14.9%" data-sdesktoppadding="9.2% 21% 15% 6%" data-ssdesktoppadding="4% 8% 25% 8%"data-mobipadding="100px 15% 100px 15%" data-smobipadding="100px 15px 100px 15px">
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
                                        <span class="numb-count numb cl-fb8122">00</span>
                                        <span class="name-count">Days</span>
                                    </div>
                                </div>
                            </li>
                            <li class="content-counter">
                                <div class="wrap-bg">
                                    <div class="inner-bg hours">
                                        <span class="numb-count numb cl-33d9b2">00</span>
                                        <span class="name-count">Hours</span>
                                    </div>
                                </div>
                            </li>
                            <li class="content-counter ">
                                <div class="wrap-bg">
                                    <div class="inner-bg seconds">
                                        <span class="numb-count numb cl-f49ac1">00</span>
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
                    <a href="#" class="btn-become">
                        Become a Sponsor
                    </a>
                </div>
            </div>
        </div>
    </div>
</section><!-- flat-event -->
<section class="special special-style2 clearfix">
    <div class="container">
        <div class="cl333">
            <div class="bg bd-left">
                <a href="#">Core values</a>
                <p>We rigorously screen and train our instructors ensure that they meet</p>
            </div>
        </div>
        <div class="cl333">
            <div class="bg bd-center active">
                <a href="#">Mission</a>
                <p>{{ substr($institution->mission, 25)  }}</p>
            </div>
        </div>
        <div class="cl333">
            <div class="bg bd-right">
                <a href="#">Vision</a>
                <p>{{ substr($institution->vision, 25)  }}</p>
            </div>
        </div>
    </div>
</section><!-- special -->
<footer id="footer" class="footer-type2">
    <div class="form-send-email">
        <div class="container">
            <form action="#" class="form-send">
                <input type="search" placeholder="Enter your email...">
                <button style="background-color: {{ $web_setting->color }}" class="btn send-button bg-clfbb545">
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
                        <img src="images/logo/02.png" alt="images">
                    </div>
                </div>
                <div class="col-lg-2 col-company">
                    <h3 class="widget widget-title text-white">
                        Company
                    </h3>
                    <ul class="widget-nav-menu">
                        <li><a href="#">About Company</a></li>
                        <li><a href="#">Feature Course</a></li>
                        <li><a href="#">Careers</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-link">
                    <h3 class="widget widget-title text-white">
                        Help Links
                    </h3>
                    <ul class="widget-nav-menu">
                        <li><a href="#">Student Support</a></li>
                        <li><a href="#">Course Policy</a></li>
                        <li><a href="#">Register Key</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-course">
                    <h3 class="widget widget-title text-white">
                        Course
                    </h3>
                    <ul class="widget-nav-menu">
                        <li><a href="#">Wordpres</a></li>
                        <li><a href="#">Photography</a></li>
                        <li><a href="#">Learning English</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-media">
                    <h3 class="widget widget-title text-white">
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
    <div id="bottom" class="bottom-type2 clearfix has-spacer">
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
