@push('styles')
   <style>
      a h3.title {
         transition: all ease-in-out 0.5s;
      }
      a h3.title:hover {
         color: var(--ecbz-primary);
      }
   </style>
@endpush

<div class="col-lg-9 col-md-9 col-12 mx-auto">
   <div class="row">
      @if($careers)
         @foreach($careers as $key => $career)
            <div class="col-lg-12 col-md-12 col-12 mb-3">
               <div class="card">
                  <div class="card-body">
                     <div class="row">
                        <div class="col-md-10 col-12">
                           <a href="{{ route('careers.show', $career->slug) }}">
                              <h3 class="title">{{ $career->title }}</h3>
                           </a>
                        </div>
                        <div class="col-md-2 col-12">
                           <span>{{ date('D, d M Y', strtotime($career->created_at)) }}</span>
                        </div>
                        <div class="col-md-12 col-12">
                           <div class="d-md-inline-flex d-block">
                              <span style="text-wrap: nowrap; font-weight: bold; margin-right:15px;">
                                 <i class="icofont-paper" style="margin-right: 5px;"></i>
                                 {{ $career->contract_type->name }}
                              </span>
                              <span style="text-wrap: nowrap; font-weight: bold; margin-right:15px;" class="me-3">
                                 <i class="icofont-location-pin" style="margin-right: 5px;"></i>
                                 {{ $career->location }}
                              </span>
                              <span style="text-wrap: nowrap; font-weight: bold; margin-right:15px;" class="me-3">
                                 <i class="icofont-ui-calendar" style="margin-right: 5px;"></i>
                                 {{ date('d M Y', strtotime($career->deadline_date)) }}
                              </span>
                           </div>
                        </div>
                        <div class="col-md-3 col-12">
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         @endforeach
            <div class="col-12">
               <div class="card">
                  <div class="card-body">
                     <div class="text-center justify-content-center">
                        <p>
                           You're viewing the <b>currently active job offers</b>. <br>
                           Check back frequently to discover new opportunities.
                        </p>
                     </div>
                  </div>
               </div>
            </div>
      @else
         <div class="col-12">
            <div class="card">
               <div class="card-body">
                  <div class="text-center justify-content-center">
                     <p>
                        You're viewing the <b>currently active job offers</b>. <br>
                        Check back frequently to discover new opportunities.
                     </p>
                  </div>
               </div>
            </div>
         </div>
      @endif
   </div>
</div>
{{--<div class="col-lg-3 col-md-3 col-12"></div>--}}
