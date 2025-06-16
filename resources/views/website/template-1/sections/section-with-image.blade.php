<section id="pabout" class="about-wel-padding">
   <div class="auto-container">
      <div class="row">
         <div class="col-lg-6 col-md-6 col-sm-12 col-12 mb-lg-0 mb-lg-0 mb-5">
            <img class="img-fluid" src="{{ $section->media[0]->original_url ?? asset('dummy-image.jpg') }}" alt="">
         </div>
         <!-- end col -->
         <div class="col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="welcome-section-title">
               <h6 class="theme-color">{{ $section->sub_title }}</h6>
               <h2>{{ $section->title }}</h2>
               <div>
                  {!! $section->details !!}
               </div>
            </div>
            <div class="welcome-des wow fadeInUp" style="visibility: visible; animation-name: fadeInUp;">
               <div class="col-lg-8 col-md-7 col-12 pl-0 mt-4">
                  <div class="row">
                     <div class="col-lg-6 mb-md-3 mb-3">
                        @if($section->has_cta_buttons && $section->cta_buttons->isNotEmpty())
                           <div class="home-single-slide-button mt-4">
                              @foreach($section->cta_buttons as $button)
                                 <a href="{{ url($button->page->slug ?? '#') }}" class="{{ $button->cta_button_type . ' mb-lg-0 mb-md-0 mb-2' }}">
                                    {{ $button->cta_button_text }}
                                    <i class="icofont-long-arrow-right"></i>
                                 </a>
                              @endforeach
                           </div>
                        @endif
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <!-- end col -->
      </div>
   </div>
</section>
