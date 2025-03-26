@php
    $classes = array_merge($classes, [
      'page__block--bg-'.((isset($content->background_colour) && $content->background_colour) ? $content->background_colour : 'white'),
    ]);
@endphp
<section class="{{ implode(' ', $classes) }}">
    <article class="holder__body">
        @include('content-templates::includes.heading')
        @include('content-templates::includes.content')

        @if (isset($content->form) && $content->form && function_exists('forms'))
            {!! forms()->load($content->form)->render() !!}
        @endif
    </article>
</section>
