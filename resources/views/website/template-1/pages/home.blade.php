@extends('website.template-1.layouts.default')

@push('meta_tags')
   {!! SEOTools::generate() !!}
@endpush

@section('page-content')
@if($sections->isNotEmpty())
   @foreach($page->sections as $section)
      @includeIf('website.template-1.sections.' . $section->type, ['section' => $section, 'customisation' => $customisation])
   @endforeach
@endif
@endsection
