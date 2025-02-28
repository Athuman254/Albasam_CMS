@extends('website.template-1.layouts.default')

@section('page-content')
    <!-- START PAGE BANNER -->
    @if($page->title != 'Home')
    <div class="page-banner page-banner-overlay" data-background="{{ asset('website-assets/template-1/assets/img/bg/mission.jpeg') }}">
        <div class="container h-100">
            <div class="row h-100">
                <div class="col-lg-12 my-auto">
                    <div class="page-banner-content text-center">
                        <h2 class="page-banner-title">{{ $page->title }}</h2>
                        <div class="page-banner-breadcrumb">
                            <p><a href="{{ route('homepage') }}">Home</a> {{ $page->title }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </div>
    @endif
    <!-- END PAGE BANNER -->

    <!-- START PAGE DATA POPULATION -->
    @if($page->sections->isNotEmpty())
        @foreach ($page->sections as $section)
            <!-- Default layout if no subsections -->
            <section id="section-{{ $section->id }}" class="section-padding @if($section->bg_style === 'color'){{ $section->bg_color }} @endif"
                     @if($section->bg_style === 'image')  data-background="{{ asset('website-assets/template-1/assets/img/bg/mission.jpeg') }}" @endif>
                <div class="auto-container">
                    <div class="row">
                        <div class="col-lg-7 col-md-7 col-12 mx-auto text-center">
                            <div class="section-title">
                                <h6 class="theme-color">{{ $section->sub_title }}</h6>
                                <h2>{{ $section->title }}</h2>
                                <p>{{ $section->content }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @if($section->subSections->isNotEmpty())
                @if ($section->subSections->count() === 2)
                    <!-- Two-subsections layout -->
                    <section id="section-{{ $section->id }}" class="section-padding">
                        <div class="auto-container">
                            <div class="row">
                                <!-- First Subsection -->
                                <div class="col-lg-6 col-md-6 col-sm-12 col-12 mb-lg-0 mb-5">
                                    @php $firstSub = $section->subSections[0]; @endphp
                                    @if ($firstSub->type == 2) {{-- Image type --}}
                                        <img class="img-fluid" src="{{ asset('website-assets/template-1/assets/img/bg/about-img.png')}}" alt=""/>
                                    @else
                                        <div class="welcome-section-title">
                                            <h6 class="theme-color">{{ $firstSub->sub_title }}</h6>
                                            <h2>{{ $firstSub->title }}</h2>
                                            <p>{{ $firstSub->content }}</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Second Subsection -->
                                <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                                    @php $secondSub = $section->subSections[1]; @endphp
                                    @if ($secondSub->type == 2) {{-- Image type --}}
                                        <img class="img-fluid" src="{{ asset('website-assets/template-1/assets/img/bg/about-img.png')}}" alt=""/>
                                    @else
                                        <div class="welcome-section-title">
                                            <h6 class="theme-color">{{ $secondSub->sub_title }}</h6>
                                            <h2>{{ $secondSub->title }}</h2>
                                            <p>{{ $secondSub->content }}</p>
                                        </div>
                                    @endif
                                </div>
                                <!-- end col -->
                            </div>
                        </div>
                    </section>
                @elseif($section->subSections->count() === 1)
                    <section id="section-{{ $section->id }}" class="section-padding @if($section->bg_style === 'color'){{ $section->bg_color }} @endif"
                             @if($section->bg_style === 'image') style="background: {{ $section->bg_style }} url('{{ asset('website-assets/template-1/assets/img/bg/about-img.png') }}') no-repeat center center;"> @endif
                        <div class="auto-container">
                            <div class="row">
                                <div class="col-lg-7 col-md-7 col-12 mx-auto text-center">
                                    <div class="section-title">
                                        @php
                                            $subSection = $section->subSections[0]; // todo: Use first() instead of [0]
                                        @endphp

                                        @if ($subSection->type == 2) {{-- Image type --}}
                                        <img class="img-fluid" src="{{ asset('website-assets/template-1/assets/img/bg/about-img.png')}}" alt=""/>
                                        @else
                                            <h6 class="theme-color">{{ $subSection->sub_title }}</h6>
                                            <h2>{{ $subSection->title }}</h2>
                                            <p>{{ $subSection->content }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @else
                    <div style="display: none;"></div>
                @endif
            @endif
      @endforeach
    @endif
    @php
    $slugs = ['contact', 'contact-us', 'reach-out'];
    @endphp
    @if(in_array($page->slug, $slugs))
        <!-- START CONTACT PAGE SECTION -->
        <section id="contcat" class="section-padding">
            <div class="auto-container">
                <div class="row">
                    <div class="col-lg-5 col-md-5 col-12 mb-lg-0 mb-md-0 mb-5">
                        <div class="address-box-wrap bg-gray shadow-sm p-lg-5 p-md-3 p-3">
                            <div class="address-box-sin mb-4">
                                <div class="address-box-icon">
                                    <i class="icofont-location-pin"></i>
                                </div>
                                <div class="address-box-des">
                                    <h4>Office Address</h4>
                                    <p>{{ $institution->city }}, {{ $institution->country }} <br> {{ $institution->physical_address }}</p>
                                </div>
                            </div>
                            <!-- end single address box -->
                            <div class="address-box-sin mb-4">
                                <div class="address-box-icon">
                                    <i class="icofont-envelope-open"></i>
                                </div>
                                <div class="address-box-des">
                                    <h4>Send Email</h4>
                                    <p>{{ $institution->email }}</p>
                                </div>
                            </div>
                            <!-- end single address box -->
                            <div class="address-box-sin mb-4">
                                <div class="address-box-icon">
                                    <i class="icofont-fax"></i>
                                </div>
                                <div class="address-box-des">
                                    <h4>Phone & Fax</h4>
                                    <p>{{ $institution->phone }}</p>
                                </div>
                            </div>
                            <!-- end single address box -->
                        </div>
                    </div>
                    <!-- end col -->
                    <div class="col-lg-7 col-md-7 col-12 pl-lg-5 pl-md-3 pl-0">
                        <div class="contact-heading mb-5">
                            <h2>Join With Us</h2>
                        </div>
                        <div class="contact-form-wrap">
                            <form id="main-form" class="contact-form form" name="enq" method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <span class="form-icon"><i class="icofont-user"></i></span>
                                            <input type="text" class="form-control" id="name" placeholder="John" required>
                                            <label for="name">First Name*</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <span class="form-icon"><i class="icofont-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" placeholder="example@xyz.com" required>
                                            <label for="email">Email*</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <span class="form-icon"><i class="icofont-ui-dial-phone"></i></span>
                                            <input type="text" class="form-control" id="number" placeholder="xxx-xxx-xxxx" required>
                                            <label for="number">Contact Number*</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <span class="form-icon"><i class="icofont-at"></i></span>
                                            <input type="text" class="form-control" id="subject" placeholder="Subject" required>
                                            <label for="subject">Subject*</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group form-message">
                                    <textarea class="form-control" id="message" rows="3" placeholder="Message"></textarea>
                                    <label for="message">Message</label>
                                </div>
                                <div class="text-center wow fadeInUp">
                                    <div class="actions">
                                        <input value="SUBMIT MESSAGE" name="submit" id="submitButton" class="btn con-btn" title="Click here to submit your message!" type="submit">
                                        <img src="assets/img/ajax-loader.gif" id="loader" style="display:none" alt="loading" width="16" height="16">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
            </div>
        </section>
        <!-- END CONTACT PAGE SECTION -->
        <!-- START MAP SECTION -->
        <div class="section-padding py-0 pt-6">
            <!-- start google map -->
            <div class="gmap_canvas">
                <iframe id="gmap_canvas" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3979.8517779926406!2d39.668697975034675!3d-4.050641195923102!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x184012c294ef02df%3A0x4e0d9a7ff36bc7f1!2sShariff%20Nassir%20Girls%20Secondary%20School!5e0!3m2!1sen!2ske!4v1738830220930!5m2!1sen!2ske" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <!-- end google map -->
        </div>
    <!-- END MAP SECTION -->
    @endif
    <!-- END PAGE DATA POPULATION -->
@endsection
