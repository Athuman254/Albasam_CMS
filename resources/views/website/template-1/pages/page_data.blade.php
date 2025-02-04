@extends('website.template-1.layouts.default')

@section('page-content')
    <!-- START PAGE BANNER -->
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
    <!-- END PAGE BANNER -->

    <!-- START PAGE DATA POPULATION -->
    @foreach ($page->sections as $section)
{{--        @if ($section->subSections->count() === 0)--}}
            <!-- Default layout if no subsections -->
            <section id="section-{{ $section->id }}" class="section-padding @if($section->bg_style === 'color'){{ $section->bg_color }} @endif"
                     @if($section->bg_style === 'image') style="background: {{ $section->bg_style }} url('{{ asset('website-assets/template-1/assets/img/bg/about-img.png') }}') no-repeat center center;"> @endif
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
{{--        @endif--}}
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
        @endif
    @endforeach
    <!-- END PAGE DATA POPULATION -->
@endsection
