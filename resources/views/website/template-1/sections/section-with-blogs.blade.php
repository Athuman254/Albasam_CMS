<section id="blog" class="section-padding">
   <div class="auto-container">
      <div class="row">
         <div class="col-lg-7 col-md-7 col-12 mx-auto text-center">
            <div class="section-title">
               <h6 class="theme-color">{{ $section->sub_title }}</h6>
               <h2>{{ $section->title }}</h2>
               <div>
                  {!! $section->details !!}
               </div>
               <div class="mt-4">
                  @foreach($section->cta_buttons as $button)
                     <a href="{{ url($button->page->slug ?? '#') }}" class="{{ $button->cta_button_type . ' mb-lg-0 mb-md-0 mb-2' }}">
                        {{ $button->cta_button_text }}
                        <i class="icofont-long-arrow-right"></i>
                     </a>
                  @endforeach
               </div>
            </div>
         </div>
      </div>
      <!-- end section title -->
      <div class="row mb-5">
         <div class="col">
            <div class="blog-slides owl-carousel owl-theme">
               @foreach($blogs->take(4) as $key => $blog)
               <div class="blog-home-single">
                  <div class="blog-home-image">
                     <img class="img-fluid" src="{{ $blog->media[0]->original_url ?? asset('dummy-image.jpg') }}" alt=""/>
                     <div class="blog-home-post-date">
                        <i class="icofont-clock-time"></i>
                        <span>{{ date('M d, Y', strtotime($blog->created_at)) }}</span>
                     </div>
                  </div>
                  <div class="blog-home-des-wrap">
                     <div class="blog-home-des-right" style="width: 100%;">
                        <div class="blog-home-meta" style="margin-top: 0;">
                           <span>Post By <a href="#">{{ $blog->user ? $blog->user->name : '' }}</a></span>
                        </div>
                        <div class="blog-home-content">
                           <h4>
                              <a href="{{ route('blogs.show', $blog->slug) }}">
                                 {{ $blog->title }}
                              </a>
                           </h4>
                           <div>
                              {!! Str::words($blog->details, 10) !!}
                           </div>
                        </div>
                        <div class="blog-home-btn">
                           <a href="{{ route('blogs.show', $blog->slug) }}"> Read More <i class="icofont-double-right"></i></a>
                        </div>
                     </div>
                  </div>
               </div>
               @endforeach
            </div>
         </div>
      </div>
   </div>
   <!--- END CONTAINER -->
</section>
