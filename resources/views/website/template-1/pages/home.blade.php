@extends('website.template-1.layouts.default')

@section('page-content')
    <!-- START SLIDER SECTION -->
    <section class="slider-section">
        <div class="home-slides owl-carousel owl-theme">
            <div class="home-single-slide"
                 data-background="{{ asset('website-assets/template-1/assets/img/bg/mission.jpeg') }}">
                <div class="home-single-slide-overlay"></div>
                <div class="home-single-slide-inner">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="home-single-slide-dec">
                                    <h4>Small class sizes</h4>
                                    <h2>Mindful Curriculum</h2>
                                    <p>we provide three teachers per section<br>to take class.</p>
{{--                                    <div class="home-single-slide-button mt-4">--}}
{{--                                        <a href="#" class="slide-btn-one mb-lg-0 mb-md-0 mb-2">Learn More <i--}}
{{--                                                    class="icofont-long-arrow-right"></i></a>--}}
{{--                                        <a href="#" class="slide-btn-two">Contact Us</a>--}}
{{--                                    </div>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end single slider -->
            <div class="home-single-slide"
                 data-background="{{ asset('website-assets/template-1/assets/img/bg/mission.jpeg') }}">
                <div class="home-single-slide-overlay"></div>
                <div class="home-single-slide-inner">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="home-single-slide-dec">
                                    <h4>Send message to parents</h4>
                                    <h2>Message Notification</h2>
                                    <p>notify parents by studied subjects and details<br>on daily basis.</p>
{{--                                    <div class="home-single-slide-button mt-4">--}}
{{--                                        <a href="#" class="slide-btn-two mb-lg-0 mb-md-0 mb-2">Our Services</a>--}}
{{--                                        <a href="#" class="slide-btn-one">Read More <i--}}
{{--                                                    class="icofont-long-arrow-right"></i></a>--}}
{{--                                    </div>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end single slider -->
            <div class="home-single-slide"
                 data-background="{{ asset('website-assets/template-1/assets/img/bg/mission.jpeg') }}">
                <div class="home-single-slide-overlay"></div>
                <div class="home-single-slide-inner">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="home-single-slide-dec">
                                    <h4>New opportunity to learn</h4>
                                    <h2>Comfort Daycare</h2>
                                    <p>notify parents by studied subjects and details<br>on daily basis.</p>
{{--                                    <div class="home-single-slide-button mt-4">--}}
{{--                                        <a href="#" class="slide-btn-one mb-lg-0 mb-md-0 mb-2">Learn More <i--}}
{{--                                                    class="icofont-long-arrow-right"></i></a>--}}
{{--                                        <a href="#" class="slide-btn-two">Contact Us</a>--}}
{{--                                    </div>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end single slider -->
        </div>
    </section>
    <!-- END SLIDER SECTION  -->

    <!-- START PAGE DATA POPULATION -->
    @foreach ($homePage->sections as $section)
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
        @if ($section->subSections->count() === 2)
            <!-- Two-subsections layout -->
            <section id="section-{{ $section->id }}" class="section-padding">
                <div class="auto-container">
                    <div class="row">
                        <!-- First Subsection -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12 mb-lg-0 mb-5 venobox">
                            @php $firstSub = $section->subSections[0]; @endphp
                            @if ($firstSub->type == 2) {{-- Image type --}}
                            <img class="img-fluid" src="{{ asset('website-assets/template-1/assets/img/bg/about-img.png')}}" alt=""/>
                            @else
                                <div class="about-wel-des">
                                    <h6 class="theme-color text-uppercase"><i class="icofont-plus"></i> {{ $firstSub->sub_title }}</h6>
                                    <h2 class="my-4">{{ $firstSub->title }}</h2>
                                    <p>{{ $firstSub->content }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Second Subsection -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12 venobox">
                            @php $secondSub = $section->subSections[1]; @endphp
                            @if ($secondSub->type == 2) {{-- Image type --}}
                            <img class="img-fluid" src="{{ asset('website-assets/template-1/assets/img/bg/about-img.png')}}" alt=""/>
                            @else
                                <div class="welcome-section-title">
                                    <h6 class="theme-color text-uppercase"><i class="icofont-plus"></i> {{ $secondSub->sub_title }}</h6>
                                    <h2 class="my-4">{{ $secondSub->title }}</h2>
                                    <p>{{ $secondSub->content }}</p>
                                </div>
                            @endif
                        </div>
                        <!-- end col -->
                    </div>
                </div>
            </section>
        @elseif($section->subSections->count() === 1)
            <section id="section-{{ $section->id }}" class="section-padding venobox @if($section->bg_style === 'color'){{ $section->bg_color }} @endif"
                     @if($section->bg_style === 'image') style="background: {{ $section->bg_style }} url('{{ asset('website-assets/template-1/assets/img/bg/about-img.png') }}') no-repeat center center;"> @endif
                <div class="auto-container">
                    <div class="row">
                        <div class="col-lg-7 col-md-7 col-12 mx-auto text-center">
                            <div class="section-title">
                                @php $subSection = $section->subSections[0]; @endphp
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
    @endforeach
    <!-- END PAGE DATA POPULATION -->
@endsection
