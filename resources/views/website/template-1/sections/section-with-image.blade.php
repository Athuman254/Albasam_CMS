<section class="section-padding">
   <div class="auto-container">
      <div class="row">
         @if($section->section_image_first)
            <div class="col-lg-6 col-md-6 col-sm-12 col-12 mb-lg-0 mb-lg-0 mb-5">
               <img class="img-fluid" src="{{ $section->media[0]->original_url ?? asset('dummy-image.jpg') }}" alt="" width="436" height="390">
            </div>
         @endif
         <!-- end col -->
         <div class="col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="welcome-section-title">
               <h6 class="theme-color">{{ $section->sub_title }}</h6>
               <h2>{{ $section->title }}</h2>
               <div>
                  {!! $section->details !!}
               </div>
            </div>
            @if($section->has_cta_buttons && $section->cta_buttons->isNotEmpty())
               <div class="welcome-des wow fadeInUp" style="visibility: visible; animation-name: fadeInUp;">
                  <div class="col-lg-12 col-md-12 col-12 pl-0 mt-4">
                     <div class="row">
                        @foreach($section->cta_buttons as $button)
                           <div class="col-lg-6 col-md-6 col-sm-4 col-12 mb-md-3 mb-1">
                              <div class="welcome-btn my-lg-4 my-2">
                                 <a href="{{ url($button->page->slug ?? '#') }}" class="{{ $button->cta_button_type }} w-100 text-center">
                                    {{ $button->cta_button_text }}
                                 </a>
                              </div>
                           </div>
                        @endforeach
                     </div>
                  </div>
               </div>
            @endif
         </div>
         <!-- end col -->
         @if(!$section->section_image_first)
            <div class="col-lg-6 col-md-6 col-sm-12 col-12 mb-lg-0 mb-lg-0 mb-5">
               <div class="float-lg-right float-md-none">
                  <img class="img-fluid" src="{{ $section->media[0]->original_url ?? asset('dummy-image.jpg') }}" alt="" width="400" height="400">
               </div>
            </div>
         @endif
      </div>
   </div>
</section>
