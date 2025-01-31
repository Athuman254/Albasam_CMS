<footer class="footer-section">
    <div id="top-footer" class="overlay-2 section-back-image-2" data-background="assets/img/bg/footer-bg.jpg">
        <div class="auto-container">
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-12 col-12 mb-lg-0 mb-md-5 mb-sm-5 mb-5">
                    <div class="footer-widget-title col-12 p-0">
                        <div class="logo">
                            <a href="{{ route('homepage') }}">
                                <img class="img-fluid" src="{{ asset('logo.png') }}" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="footer-widget-inner">
                        <p>{{ $institution->mission }}</p>
                        <div class="img-menu float-lg-left float-none mt-3">
                            <div class="footer-social">
                                <ul>
                                    <li><a class="social-fb" href="#"><i class="icofont-instagram"></i></a></li>
                                    <li><a class="social-tw" href="#"><i class="icofont-twitter"></i></a></li>
                                    <li><a class="social-gp" href="#"><i class="icofont-youtube"></i></a></li>
                                    <li><a class="social-fb" href="#"><i class="icofont-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end col -->
                <div class="col-lg-3 col-md-6 col-sm-12 col-12 mb-lg-0 mb-md-5 mb-sm-5 mb-5">
                    <div class="footer-widget-title col-12 p-0">
                        <h4>Latest Post</h4>
                    </div>
                    <div class="footer-widget-inner">
                        <div class="singleRecpost">
                            <img src="{{ asset('website-assets/template-1/assets/img/bg/mission.jpeg') }}" alt="" class="img-fluid">
                            <h6 class="recTitle">
                                <a href="#">Designing Learner-Centered Classroom</a>
                            </h6>
                            <p class="posted-on">18 MAY 2021</p>
                        </div>
                        <div class="singleRecpost">
                            <img src="{{ asset('website-assets/template-1/assets/img/bg/mission.jpeg') }}" alt="" class="img-fluid">
                            <h6 class="recTitle">
                                <a href="#">Building an environment for learning</a>
                            </h6>
                            <p class="posted-on">17 MAY 2021</p>
                        </div>
                    </div>
                </div>
                <!-- end col -->
                <div class="col-lg-3 col-md-6 col-sm-12 col-12 mb-lg-0 mb-md-0 mb-sm-5 mb-5">
                    <div class="footer-widget-title col-12 p-0">
                        <h4>Useful Links</h4>
                    </div>
                    <div class="footer-widget-inner">
                        <ul>
                            <li><a href="#"><i class="icofont-circled-right"></i> Our Classes</a></li>
                            <li><a href="#"><i class="icofont-circled-right"></i> Latest Services</a></li>
                            <li><a href="#"><i class="icofont-circled-right"></i> Our Teachers</a></li>
                            <li><a href="#"><i class="icofont-circled-right"></i> Image Gallery</a></li>
                            <li><a href="#"><i class="icofont-circled-right"></i> Frequently Question</a></li>
                            <li><a href="#"><i class="icofont-circled-right"></i> Client Testimonial</a></li>
                        </ul>
                    </div>
                </div>
                <!-- end col -->
                <div class="col-lg-3 col-md-6 col-sm-12 col-12 mb-lg-0 mb-md-0 mb-sm-0 mb-0">
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
                    <p class="copyright-text">Copyright © {{ now()->format('Y') }} <a href="#">Ecobiz</a> | All Rights Reserved</p>
                </div>
            </div>
        </div>
    </div>
</footer>
