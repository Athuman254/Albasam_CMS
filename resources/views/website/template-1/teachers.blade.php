<div class="row mb-lg-5 mb-0">
   <div class="col">
      <div class="team-slides owl-carousel owl-theme">
         @foreach($teachers as $key => $teacher)
            <div class="single-team-wrapper">
               <div class="single-team-member">
                  <img class="img-fluid" src="{{ asset('website/images/team-dummy.jpg') }}" alt="">
                  <div class="single-team-member-content">
                     <ul class="single-team-member-social">
                        <li><a href="#"><i class="icofont-facebook"></i></a></li>
                        <li><a href="#"><i class="icofont-twitter"></i></a></li>
                        <li><a href="#"><i class="icofont-pinterest"></i></a></li>
                        <li><a href="#"><i class="icofont-youtube"></i></a></li>
                     </ul>
                     <div class="single-team-member-text">
                        <h4>{{ $teacher->first_name . ' ' . $teacher->last_name }}</h4>
                        <p style="visibility: hidden">Teacher</p>
                     </div>
                  </div>
               </div>
            </div>
            <!-- End single team item -->
         @endforeach
      </div>
   </div>
</div>
