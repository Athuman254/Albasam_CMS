<section class="section-padding">
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
      </div>
      <div class="row mb-lg-5 mb-0">
         <div class="col">
            <div class="team-slides owl-carousel owl-theme">
               @foreach($teachers->take(4) as $key => $teacher)
                  <div class="single-team-wrapper">
                     <div class="single-team-member">
                        <img class="img-fluid" src="{{ asset('website/images/team-dummy.jpg') }}" alt="" width="400" height="400">
                        <div class="single-team-member-content">
                           <ul class="single-team-member-social">
                              <li><a href="#"><i class="icofont-facebook"></i></a></li>
                              <li><a href="#"><i class="icofont-twitter"></i></a></li>
                              <li><a href="#"><i class="icofont-pinterest"></i></a></li>
                              <li><a href="#"><i class="icofont-youtube"></i></a></li>
                           </ul>
                           <div class="single-team-member-text">
                              <h4>{{ $teacher->first_name . ' ' . $teacher->last_name }}</h4>
                           </div>
                        </div>
                     </div>
                  </div>
                  <!-- End single team item -->
               @endforeach
            </div>
         </div>
      </div>
   </div>
</section>
