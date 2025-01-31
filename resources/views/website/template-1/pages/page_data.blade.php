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
@endsection
