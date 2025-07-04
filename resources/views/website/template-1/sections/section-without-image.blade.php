<section class="section-padding">
   <div class="auto-container">
      <div class="row">
         <div class="col-lg-10 col-md-8 col-12 mx-auto text-center">
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
   </div>
</section>
