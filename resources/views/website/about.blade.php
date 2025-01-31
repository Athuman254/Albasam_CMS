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

    @include("website.shared.header");

    <div class="flat-about pd-about clearfix">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="textbox-about">
                        <div class="title-section">
                            <div class="flat-title medium heading-type18">
                                About our education!
                            </div>
                        </div>
                        <div class="textbox-content">
                            <div class="about-introduce">
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
                                <div class="btn-about">
                                    <a style="color: {{ $web_setting->color }}" href="#mission" class="btn-box-shadow">read more</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="iconbox-about">
                        <div class="iconbox-about-wrap clearfix">
                            <div class="list-1">
                                <div class="iconbox iconbox-teacher">
                                    <div class="counter">
                                        <div class="content-counter">
                                            <div class="numb-count bg-cl25cf71" data-from="0" data-to="50" data-speed="2000" data-inviewport="yes">50</div>
                                            <div class="name-count">Teacher</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="iconbox iconbox-students">
                                    <div class="counter">
                                        <div class="content-counter">
                                            <div class="numb-count bg-cla476b4" data-from="0" data-to="1736" data-speed="2000" data-inviewport="yes">1736</div>
                                            <div class="name-count">Students</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="list-2">
                                <div class="iconbox iconbox-courses">
                                    <div class="counter">
                                        <div class="content-counter">
                                            <div class="numb-count bg-clffbe34" data-from="0" data-to="350" data-speed="2000" data-inviewport="yes">350</div>
                                            <div class="name-count">Classrooms</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- flat-about -->
    <div id="mission" class="edukin-introduce equalize sm-equalize-auto clearfix">
        <div class="element-col50 bg-element1 element-text ">
            <div class="bg-over">
                <div class="title-section">
                    <div class="flat-title medium heading-type18 text-white">
                        Mission!
                    </div>
                </div>
                <p>
                    Education is about teaching, learning skills & knowledge. Education also mean helping people learn how to do things and encouraging them to think. eduking the best psd template.
                </p>
                <p>
                    Education gives us a knowledge of world around us and changes it into something better.It develops in us perspective of looking at life. It helps us build opinions and have points of view.
                </p>
            </div>

        </div>
        <div class="element-col50 bg-element2 element-bg">
            <div class="flat-spacer" data-desktop="0" data-mobi="400" data-smobi="300"></div>
        </div>
        <div class="element-col50 bg-element3 element-bg">
            <div class="flat-spacer" data-desktop="0" data-mobi="400" data-smobi="300"></div>
        </div>
        <div class="element-col50 bg-element4 element-text">
            <div class="bg-over">
                <div class="title-section">
                    <div class="flat-title medium heading-type19 text-white">
                        Vission
                    </div>
                </div>
                <p>
                    Education is about teaching, learning skills & knowledge. Education also mean helping people learn how to do things and encouraging them to think. eduking the best psd template.
                </p>
                <p>
                    Education gives us a knowledge of world around us and changes it into something better.It develops in us perspective of looking at life. It helps us build opinions and have points of view.
                </p>
            </div>

        </div>
    </div><!-- edukin-introduce -->

    {{-- <div class="flat-team mg-flat-team">
        <div class="container">
            <div class="section-heading">
                <div class="title-section text-center">
                    <div class="flat-title medium heading-type20">Our Teachers</div>
                </div>
                <p class="caption text-center">
                    Constant self-improvement as an instructor imperative. How do you challenging yourself & seeking to improve,make your lesson relevant to their lives
                </p>
            </div>
            <div class="pd-list-team">
                <div class="flat-carousel-box data-effect clearfix" data-gap="30" data-column="4" data-column2="2" data-column3="1" data-column4="1" data-dots="false" data-auto="false" data-nav="false">
                    <div class="owl-carousel">
                        <div class="team-box-layout-h1">
                            <div class="item-img">
                                <img src="images/about/5.jpg" alt="images" class="img-fluid">
                            </div>
                            <div class="item-content">
                                <div class="item-title">
                                    <a href="#">Adriana Nola</a>
                                </div>
                                <div class="item-subtitle">Teacher</div>
                                <ul class="item-social">
                                    <li>
                                        <a href="#"><i class="fa fa-facebook-f" aria-hidden="true"></i> </a>
                                    </li>
                                    <li>
                                        <a href="#"><i class="fa fa-twitter" aria-hidden="true"></i> </a>
                                    </li>
                                    <li>
                                        <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="team-box-layout-h1">
                            <div class="item-img"> <img src="images/about/6.jpg" alt="images" class="img-fluid"></div>
                            <div class="item-content">
                                <div class="item-title">
                                    <a href="#">Tom Brave</a>
                                </div>
                                <div class="item-subtitle">Teacher</div>
                                <ul class="item-social">
                                    <li>
                                        <a href="#"><i class="fa fa-facebook-f" aria-hidden="true"></i> </a>
                                    </li>
                                    <li>
                                        <a href="#"><i class="fa fa-twitter" aria-hidden="true"></i> </a>
                                    </li>
                                    <li>
                                        <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="team-box-layout-h1">
                            <div class="item-img"> <img src="images/about/7.jpg" alt="images" class="img-fluid"></div>
                            <div class="item-content">
                                <div class="item-title">
                                    <a href="#">Sariah Jocelynn</a>
                                </div>
                                <div class="item-subtitle">Assistant</div>
                                <ul class="item-social">
                                    <li>
                                        <a href="#"><i class="fa fa-facebook-f" aria-hidden="true"></i> </a>
                                    </li>
                                    <li>
                                        <a href="#"><i class="fa fa-twitter" aria-hidden="true"></i> </a>
                                    </li>
                                    <li>
                                        <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="team-box-layout-h1">
                            <div class="item-img"> <img src="images/about/8.jpg" alt="images" class="img-fluid"></div>
                            <div class="item-content">
                                <div class="item-title">
                                    <a href="#">Hayden Peyton</a>
                                </div>
                                <div class="item-subtitle">Teacher</div>
                                <ul class="item-social">
                                    <li>
                                        <a href="#"><i class="fa fa-facebook-f" aria-hidden="true"></i> </a>
                                    </li>
                                    <li>
                                        <a href="#"><i class="fa fa-twitter" aria-hidden="true"></i> </a>
                                    </li>
                                    <li>
                                        <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}



    <footer id="footer" class="footer-type1">
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
        @include('website.shared.homefooter')
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
