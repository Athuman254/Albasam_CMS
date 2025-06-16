<!-- START SLIDER SECTION -->
<section class="slider-section">
   <div class="home-slides owl-carousel owl-theme">
      <div class="home-single-slide"
           data-background="{{ $section->media[0]->original_url ?? asset('dummy-image.jpg') }}">
         <div class="home-single-slide-overlay"></div>
         <div class="home-single-slide-inner">
            <div class="container">
               <div class="row">
                  <div class="col-lg-8">
                     <div class="home-single-slide-dec">
                        <h4>{{ $section->sub_title }}</h4>
                        <h2>{{ $section->title }}</h2>
                        <div>
                           {!! $section->details !!}
                        </div>
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
      </div>
   </div>
</section>
<!-- END SLIDER SECTION  -->
