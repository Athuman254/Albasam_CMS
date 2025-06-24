<section id="calltoactiontwo" class="callto-action-padding bg-theme">
    <div class="auto-container">
        <div class="row">
            <div class="col-lg-9 col-md-6 col-12 mb-lg-0 mb-4">
                <div class="callto-action-left">
                    <h2>Online Admission is going On</h2>
                    <p>Visit the link to access the admission form. All admissions should be done online.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12 mt-3 text-lg-right text-md-right text-left">
                <a href="#" class="call-to-action-btn-2 wow fadeInUp">Admission Now </a>
            </div>
        </div>
    </div>
</section>
<footer class="footer-section">
    <div id="top-footer" class="overlay-2 section-back-image-2">
        <div class="auto-container">
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-12 col-12 mb-lg-0 mb-md-5 mb-sm-5 mb-5">
                    <div class="footer-widget-title col-12 p-0">
                        <div class="logo">
                            <a href="#">
                                <img class="img-fluid" src="{{ $logo ?? '' }}" alt="logo">
                            </a>
                        </div>
                    </div>
                    <div class="footer-widget-inner">
                        <p>{{ $institution->mission ?? '' }}</p>
                        <div class="img-menu float-lg-left float-none mt-3">
                            <div class="footer-social">
                                <ul>
                                @if($institution->fb_profile)
                                    <li><a class="social-fb" href="{{ $institution->fb_profile }}"><i class="icofont-facebook"></i></a>
                                    </li>
                                @endif
                                @if($institution->ig_profile)
                                    <li><a class="social-gp" href="{{ $institution->ig_profile }}"><i class="icofont-instagram"></i></a></li>
                                @endif
                                @if($institution->x_profile)
                                    <li><a class="social-tw" href="{{ $institution->x_profile }}"><i class="icofont-twitter"></i></a></li>
                                @endif
                                @if($institution->youtube_profile)
                                    <li><a class="social-gp" href="{{ $institution->youtube_profile }}"><i class="icofont-youtube"></i></a></li>
                                @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end col -->
                <div class="col-lg-4 col-md-6 col-sm-12 col-12 mb-lg-0 mb-md-5 mb-sm-5 mb-5">
                    <div class="footer-widget-title col-12 p-0">
                        <h4>Latest Blogs</h4>
                    </div>
                    <div class="footer-widget-inner">
                        @foreach($blogs->take(2) as $key => $blog)
                        <div class="singleRecpost">
                            <img src="{{ $blog->media[0]->original_url ?? asset('dummy-image.jpg') }}" alt="" class="img-fluid">
                            <h6 class="recTitle">
                                <a href="{{ route('blogs.show', $blog->slug) }}">{{ $blog->title }}</a>
                            </h6>
                            <p class="posted-on">{{ date('d M Y', strtotime($blog->created_at)) }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                <!-- end col -->
                <div class="col-lg-4 col-md-6 col-sm-12 col-12 mb-lg-0 mb-md-0 mb-sm-0 mb-0">
                    <div class="footer-widget-title col-12 p-0">
                        <h4>Get In Touch</h4>
                    </div>
                    <div class="footer-widget-inner">
                        <div class="footer-contact-widget">
                            <div class="footer-contact-sin">
                                <div class="footer-contact-sin-left">
                                    <i class="icofont-pin"></i>
                                </div>
                                <div class="footer-contact-sin-right">
                                    <p>{{ $institution->physical_address }}</p>
                                </div>
                            </div>
                            <div class="footer-contact-sin">
                                <div class="footer-contact-sin-left">
                                    <i class="icofont-smart-phone"></i>
                                </div>
                                <div class="footer-contact-sin-right">
                                    <p>{{ $institution->phone }}</p>
                                </div>
                            </div>
                            <div class="footer-contact-sin">
                                <div class="footer-contact-sin-left">
                                    <i class="icofont-envelope"></i>
                                </div>
                                <div class="footer-contact-sin-right">
                                    <p>{{ $institution->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end col -->
            </div>
        </div>
    </div>
    <div id="bottom-footer" class="bg-gray">
        <div class="auto-container">
            <div class="row mb-lg-0 mb-md-4 mb-4">
                <div class="col-lg-6 col-md-12 col-12">
                    <p class="copyright-text">Copyright © {{ now()->format('Y') }} <a href="https://ecobiz.co.ke" target="_blank">Ecobiz</a> | All Rights Reserved</p>
                </div>
            </div>
        </div>
    </div>
</footer>
