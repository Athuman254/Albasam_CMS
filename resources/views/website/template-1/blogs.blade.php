@foreach($blogs as $key => $blog)
   <div class="col-lg-4 col-md-4 col-12">
      <div class="service-list-item shadow">
         <div class="service-list-img">
            <img class="img-fluid" src="{{ $blog->media[0]->original_url ?? asset('dummy-image.jpg') }}" alt="">
            <div class="mask mask-1"></div>
            <div class="mask mask-2"></div>
            <div class="content">
               <a href="{{ route('blogs.show', $blog->slug) }}" class="info">Read More</a>
            </div>
         </div>
         <div class="service-list-des">
            <h4>
               <a href="{{ route('blogs.show', $blog->slug) }}">
                  <i class="icofont-paper"></i>{{ Str::words($blog->title, 3) }}
               </a>
            </h4>
            <div>
               {!! Str::words($blog->details, 15) !!}
            </div>
         </div>
      </div>
   </div>
@endforeach
