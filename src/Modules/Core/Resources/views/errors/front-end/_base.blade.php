@php
  $page->content = refinedErrorHandler()->pageContent($page, $__env->yieldContent('message'));
@endphp

{{-- the site's general template gives the error the full page shell, layouts.index is the fallback --}}
@section('template')
  {!! $page->content !!}
@stop

@include(view()->exists('templates::general') ? 'templates::general' : 'layouts.index')
