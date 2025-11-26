@extends('website.template-1.layouts.default')

@push('styles')
   <style>
      ul {
         list-style: none;
      }
      .job-details p {
         /*color: #15141A;*/
         margin-bottom: 8px;
      }
      .job-details ul {
         margin-left: 25px;
      }
      .job-details li {
         list-style: square;
      }
      .job-details ul li p {
         margin-bottom: 0;
      }
   </style>
@endpush

{{--@push('meta_tags')--}}
{{--   {!! SEOTools::generate() !!}--}}
{{--@endpush--}}

@section('page-content')
   @include('website.template-1.layouts.shared.banner', ['title' => $career->title])

   <section class="section-padding">
      <div class="auto-container">
         <div class="row mb-lg-5 mb-0">
            <div class="col-lg-8 col-md-12 col-12">
               <div class="title mb-3">
                  <h3>{{ $career->title }}</h3>
               </div>
               <ul class="mb-3">
                  <li>
                     <i class="icofont-paper" style="margin-right: 5px;"></i>
                     <span style="font-weight:bold; margin-right:5px;">Contract type: </span>
                     {{ $career->contract_type->name }}
                  </li>
                  <li>
                     <i class="icofont-location-pin" style="margin-right: 5px;"></i>
                     <span style="font-weight:bold; margin-right:5px;">Location: </span>
                     {{ $career->location }}
                  </li>
                  <li>
                     <i class="icofont-ui-calendar" style="margin-right: 5px;"></i>
                     <span style="font-weight:bold; margin-right:5px;">Published on: </span>
                     {{ date('d M Y', strtotime($career->created_at)) }}
                  </li>
                  <li>
                     <i class="icofont-ui-calendar" style="margin-right: 5px;"></i>
                     <span style="font-weight:bold; margin-right:5px;">Deadline date: </span>
                     {{ date('d M Y', strtotime($career->deadline_date)) }}
                  </li>
               </ul>

               <div class="title mb-3">
                  <h3>Job Description</h3>
               </div>
               <div class="job-details">
                  {!! $career->job_description !!}
               </div>
            </div>
         </div>
      </div>
   </section>
@endsection
