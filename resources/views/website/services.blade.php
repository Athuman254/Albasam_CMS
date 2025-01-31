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
    <style>
        .pagination {
display: flex;
justify-content: center;
margin: 30px 0;
padding: 0;
}


.pagination li {
list-style: none;
margin: 0 5px;
}
.pagination .hidden{
display: none;
}


.pagination li a {
display: block;
padding: 8px 16px;
border: 1px solid #e2e8f0;
border-radius: 6px;
color: #4a5568;
text-decoration: none;
transition: all 0.3s ease;
}


.pagination li a:hover {
background-color: #f7fafc;
color: #2d3748;
}


.pagination li.active span {
display: block;
padding: 8px 16px;
background-color: #4299e1;
border: 1px solid #4299e1;
border-radius: 6px;
color: white;
}


.pagination li.disabled span {
display: block;
padding: 8px 16px;
border: 1px solid #edf2f7;
border-radius: 6px;
color: #a0aec0;
cursor: not-allowed;
}
    </style>
    @include('website.shared.header');
    <div class="course-list mg-top1 clearfix">
        <div class="container">
            <div class="col-30">
                <div class="remove-filter">
                    <a href="#"><span class="icon-quote icon-icons8-delete-100"></span></a>
                    <span class="remove">Filter</span>
                </div>
                <div class="sidebar-left sidebar-course">
                    <div class="widget widget-categories">
                        <div class="widget-title">
                            Category
                            <span class="bg">
                                <i class="fa fa-angle-down" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="content">
                            <ul>
                                @foreach ($categories as $category)
                                <li>
                                    <a href="?category={{ $category->name }}">{{ $category->name }}</a>
                                    {{-- <span class="numb-left">25</span> --}}
                                </li>
                                @endforeach

                            </ul>
                        </div>
                    </div>
                </div><!-- sidebar-left -->
            </div>
            <div class="col-70">
                <div class="content-course-list">
                    @foreach ($services as $service)
                    <div class="flat-course clearfix">
                        <div class="featured-post">
                            <div class="entry-image">
                                <img src="{{ '/storage/'.$service->temp_image ?? '/images/course-list/1.jpg' }}" alt="images">
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
                                        <span class="price-now">ksh{{ $service->price }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
                <div class=" pagination">

                    <ul class="pagination">
                        {{ $services->links() }}
                    </ul>

            </div>
            </div>
        </div>
    </div><!-- course-list -->
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
        <div id="footer-widget">
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
