@push('styles')
   <style>
      .single-blog-post-content p {
         margin: 5px 0;
      }
   </style>
@endpush

@extends('website.template-1.layouts.default')

@section('page-content')
   @include('website.template-1.layouts.shared.banner', ['title' => $blog->title])

   <section id="blogsingle" class="section-padding">
      <div class="auto-container">
         <div class="row mb-lg-5 mb-0">
            <div class="col-lg-8 col-md-8 col-12">
               <div class="blog-single single-blog-post">
                  <div class="single-blog-post-wrap">
                     <div class="single-blog-post-icon">
                        <i class="icofont-photobucket"></i>
                     </div>
                     <div class="single-blog-post-content">
                        <h4 class="single-blog-post-title">
                           <span>{{ $blog->title }}</span>
                        </h4>
                        <div class="single-blog-post-Info">
                           <span><i class="icofont-user"></i>{{ $blog->user ? $blog->user->name : '' }}</span>
                           <small>/</small>
                           <span><i class="icofont-calendar"></i>{{ date('M d, Y', strtotime($blog->created_at)) }}</span>
                        </div>
                        <div class="single-blog-post-img">
                           <img class="img-fluid" src="{{ $blog->media[0]->original_url ?? asset('dummy-image.jpg') }}" alt="">
                        </div>
                        <div class="blog-single-des mt-4">
                           <h4 class="title">Blog Overview</h4>
                           <div>
                              {!! $blog->details !!}
                           </div>
                        </div>
                     </div>
                  </div>

                  <div class="blog-single-prevnxt">
                     <div class="blog-single-prevnxt-wrap">
                        <div class="blog-single-prevnxt-icon">
                           <i class="icofont-ui-previous"></i>
                        </div>
                        <div class="blog-single-related-post">
                           <div class="row">
                              @if ($previousBlog)
                                 <div class="post-previous">
                                    <span>Previous Blog</span>
                                    <h6>
                                       <a href="{{ route('blogs.show', $previousBlog->slug) }}" data-toggle="tooltip"
                                          title="View previous post">{{ $previousBlog->title }}</a>
                                    </h6>
                                 </div>
                              @endif
                              <div class="col-lg-6 col-md-6 col-12">
                                 @if ($nextBlog)
                                    <div class="post-next">
                                       <span>Next Blog</span>
                                       <h6>
                                          <a href="{{ route('blogs.show', $nextBlog->slug) }}" data-toggle="tooltip"
                                             title="View next post">{{ $nextBlog->title }}</a>
                                       </h6>
                                    </div>
                                 @endif
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="col-lg-4 col-md-4 col-12 mt-lg-0 mt-md-0 mt-5 pl-lg-5 pl-md-5 pl-0">
               <div class="sidebar-widget post_wid mb-5">
                  <div class="sidebar-widget-inner">
                     <div class="sidebar-widget-title">
                        <h5>Recent Blogs</h5>
                     </div>
                     @foreach($otherBlogs as $key => $other)
                        <div class="singleRecpost">
                           <img src="{{ $other->media[0]->original_url ?? asset('dummy-image.jpg') }}" alt="" class="img-fluid">
                           <h6 class="recTitle">
                              <a href="{{ route('blogs.show', $other->slug) }}">{{ $other->title }}</a>
                           </h6>
                           <p class="posted-on">{{ date('d M Y', strtotime($other->created_at)) }}</p>
                        </div>
                     @endforeach
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
@endsection
