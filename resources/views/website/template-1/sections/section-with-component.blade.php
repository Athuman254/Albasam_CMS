<section id="servicelist" class="section-padding">
   <div class="auto-container">
      <div class="row">
         @if($section->title)
         <div class="col-lg-7 col-md-7 col-12 mx-auto text-center">
            <div class="section-title">
               @if($section->sub_title)
                  <h6 class="theme-color">{{ $section->sub_title }}</h6>
               @endif
               <h2>{{ $section->title }}</h2>
               @if($section->details)
                  <div>
                     {!! $section->details !!}
                  </div>
               @endif
               @if($section->cta_buttons->count() > 0)
                  <div class="mt-4">
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
         @endif
         <div class="col-12">
            <div class="row">
               @includeIf('website.template-1.' . $section->component_type, ['section' => $section, 'customisation' => $customisation])
            </div>
         </div>
      </div>
   </div>
</section>
