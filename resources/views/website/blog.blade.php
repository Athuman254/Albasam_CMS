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
    @include("website.shared.header");
    <div class="blog-bl content-blog">
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
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="site-content">
                        @foreach ($posts as $post)
                        <article class="post-blog box-shadow-type2">
                            <div class="featured-post">
                                <img src="{{ '/storage/'.$post->featured_image ??"/images/blog/01.png" }}" alt="images">
                            </div>
                            <div class="content-post content-post-blog">
                                <div class="post-meta">
                                    <div class="clendar-wrap">
                                        @php
                                            $timestamp = strtotime($post->created_at);
                                            $formattedDate = date('d M Y', $timestamp);
                                            $formattedDate = explode(' ',$formattedDate);
                                        @endphp
                                        <div class="day">
                                           {{ $formattedDate[0] ?? '00' }}
                                        </div>
                                        <div class="month">
                                            {{ strtoupper($formattedDate[1] ?? 'NULL' ) }}
                                        </div>
                                    </div>
                                    {{-- <ul class="social">
                                        <li><i class="fa fa-facebook" aria-hidden="true"></i></li>
                                        <li><i class="fa fa-instagram" aria-hidden="true"></i></li>
                                        <li><i class="fa fa-twitter" aria-hidden="true"></i></li>
                                    </ul> --}}
                                </div>
                                <div class="content-post-inner">
                                    <div class="poster lt-sp0029">
                                        Posted by <span><a href="/blog/{{ $post->id }}">Admin</a></span>
                                    </div>
                                    <h3 class="entry-title">
                                        <a href="/blog/{{ $post->id }}" class="lt-sp0023">{{ $post->title }}</a>
                                    </h3>
                                   {!! $post->content !!}
                                    <div class="flat-button">
                                        <a href="/blog/{{ $post->id }}">Read Now</a>
                                    </div>
                                </div>
                            </div>
                        </article>
                        @endforeach
                    </div>
                    <div class=" pagination">

                            <ul class="pagination">
                                {{ $posts->links() }}
                            </ul>

                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="sidebar">
                        <div class="widget widget-search">
                            <div class="search-blog-wrap">
                                <form action="" class="search-form">
                                    <input name="search" type="search" placeholder="Search here ....">
                                    <button style="background-color: {{ $web_setting->color }}" class="search-button">
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="widget widget-categories">
                            <h4 class="widget-title">
                                <span>Categories</span>
                            </h4>

                                <ul class="categories-wrap">
                                    @foreach ($categories as $category)
                                        <li><a href="?category={{ $category->name }}">{{ $category->name }}</a></li>
                                    @endforeach

                                </ul>

                        </div>
                        <div class="widget widget-sent">
                            <div class="apply-admission">
                                <div style="background-color: {{ $web_setting->color }}" class="apply-admission-wrap type1 bd-type1">
                                    <div class="apply-admission-inner">
                                        <h2 class="title text-center">
                                            <span>Apply for admission</span>
                                        </h2>
                                        <div class="caption text-center text-white">
                                            Make it more simple!
                                        </div>
                                        <div class="apply-sent apply-sent-style1">
                                            <form method="POST" action="/request-admission" class="form-sent">
                                                @csrf
                                                <input hidden type="text" name="name">
                                                <input type="email" name="email" placeholder="Enter your email ...." autocomplete>
                                                <button class="sent-button bg-cl183251">
                                                    <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="widget widget-latest-post">
                            <h4 class="widget-title">
                                <span>Latest Post</span>
                            </h4>
                            <div class="latest-post">
                                <ul class="latest-post-wrap">
                                    @foreach ($latest_post as $post)
                                    <li>
                                        <div style="width: 70px; height:70px" class="thumb-new">
                                            <img class="w-100" src="{{ '/storage/'.$post->temp_image }}" alt="images">
                                        </div>
                                        <div class="thumb-new-content clearfix">
                                            <h6 class="thumb-new-title">
                                                <a href="#">
                                                    {{ $post->title }}
                                                </a>
                                            </h6>
                                            <p class="thumb-new-day">
                                               {{ $post->published_at }}
                                            </p>
                                        </div>
                                    </li>

                                    @endforeach

                                </ul>
                            </div>
                        </div>
                        {{-- <div class="widget widget-instagram-post">
                            <h4 class="widget-title">
                                <span>Instagram</span>
                            </h4>
                            <div class="news-block">
                                <div class="w-content news-block-content news-block-content-cus">
                                    <ul>
                                        <li><img src="images/blog-sidebar/04.png" alt="images"></li>
                                        <li><img src="images/blog-sidebar/06.png" alt="images"></li>
                                        <li><img src="images/blog-sidebar/06.png" alt="images"></li>
                                        <li><img src="images/blog-sidebar/04.png" alt="images"></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="free-education">
                                <div class="free-education-wrap">
                                    <p class="cl-fff text-center">
                                        The Best online Education Theme
                                    </p>
                                    <h3 class="cl-fff text-center">
                                        Free Education
                                    </h3>
                                    <div class="order-now">
                                        <a href="#" class="btn btn-oder-now">OREDER NOW</a>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div><!-- content-blog -->

    @include('website.shared.footer');

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
