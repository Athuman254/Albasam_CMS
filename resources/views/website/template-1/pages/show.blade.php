@extends('website.template-1.layouts.default')

@section('page-content')
    @include('website.template-1.layouts.shared.banner', ['title' => $page->title])

    @if($sections->isNotEmpty())
        @foreach($page->sections as $section)
            @includeIf('website.template-1.sections.' . $section->type, ['section' => $section, 'customisation' => $customisation])
        @endforeach
    @endif

@endsection
