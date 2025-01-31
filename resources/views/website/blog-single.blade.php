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
    <div class="blog-single content-blog">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="site-content clearfix">
                        <article class="post post-blog-single">
                            <div class="featured-post post-img">
                                <img src="/storage/{{ $blog_post->featured_image }}" alt="images">
                            </div>
                            <div class="content-blog-single">
                                {{-- <ul class="social social-blog-single pd-top8">
                                    <li><i class="fa fa-facebook" aria-hidden="true"></i></li>
                                    <li><i class="fa fa-twitter" aria-hidden="true"></i></li>
                                    <li><i class="fa fa-instagram" aria-hidden="true"></i></li>
                                    <li><i class="fa fa-google-plus" aria-hidden="true"></i></li>
                                </ul> --}}
                                <div class="content-blog-single-inner">
                                    <div class="content-blog-single-wrap">
                                        <h1 class="title pd-title-single">
                                            <a href="#">{{ $blog_post->title }}</a>
                                        </h1>

                                        {!! $blog_post->content !!}

                                        <div class="blog-single-poster">
                                            <div class="blog-single-poster-wrap">
                                                <div class="avtar-poster">
                                                    {{-- <img src="/storage/{{ $blog_post->featured_image }}" alt="images"> --}}
                                                </div>
                                                <div class="info-poster">
                                                    <div class="name">
                                                        Admin
                                                    </div>
                                                    <div class="position">
                                                        {{ $blog_post->created_at }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($blog_post->comment_status == 'opaen')
                                    <div id="respond" class="comment-respond">
                                        <h3 id="reply-title" class="comment-reply-title mg-bottom24">
                                            Post A Comment
                                        </h3>
                                        <form action="#" method="post" class="comments">
                                            <div class="text-wrap clearfix">
                                                <div class="full-name-wrap">
                                                    <input type="text" class="full-name" placeholder="Full name">
                                                </div>
                                                <div class="email-address-wrap">
                                                    <input type="text" class="email-address" placeholder="Email Address">
                                                </div>
                                            </div>
                                            <div class="message-wrap">
                                                <textarea name="comment" id="comment-message" rows="8" placeholder="Type here Message"></textarea>
                                            </div>
                                            <div class="mg-top30">
                                                <button class="btn btn-post-comment box-shadow-type1">
                                                    Post Comment
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    @endif

                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="related-posts">
                        <div class="title">
                            Related Posts
                        </div>
                        <div class="flat-testimonial-carousel">
                            <div class="flat-carousel-box data-effect clearfix" data-gap="30" data-column="2" data-column2="2" data-column3="1"  data-column4="1" data-dots="false" data-auto="true">
                                <div class="owl-carousel">
                                    @foreach ($related_posts as $post)
                                    <article class="post related-posts-wrap">
                                        <div class="post-border">
                                            <div class="featured-post">
                                                <img src="/storage/{{ $post->featured_image }}" alt="images">
                                            </div>
                                            <div class="content-related-post">
                                                <div class="related-post-inner">
                                                    <h5 class="post-title lt-sp025">
                                                        <a href="/blog/{{ $post->id }}">{{ substr($post->title,0,15)  }}</a>
                                                    </h5>
                                                    <div class="text">
                                                       {{ substr($post->content,3,10)}}
                                                    </div>
                                                    <div class="read-more">
                                                        <a href="/blog/{{ $post->id }}">Read more</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="sidebar">
                        <div class="widget widget-search">
                            <div class="search-blog-wrap">
                                <form action="#" class="search-form">
                                    <input type="search" placeholder="Search here ....">
                                    <button class="search-button">
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
                                    <li><a href="#">{{ $category->name }}</a></li>
                                @endforeach

                            </ul>
                        </div>
                        <div class="widget widget-sent">
                            <div class="apply-admission">
                                <div class="apply-admission-wrap type1 bd-type1">
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

                    </div>
                </div>
            </div>
        </div>
    </div><!-- blog-single -->
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
