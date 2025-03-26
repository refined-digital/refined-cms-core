@php
    $classes = array_merge($classes, [
      'page__block--bg-'.((isset($content->background_colour) && $content->background_colour) ? $content->background_colour : 'white'),
    ]);
@endphp
<section class="{{ implode(' ', $classes) }}">
    <div class="holder">
        <article class="holder__body">
            @include('content-templates::includes.heading')
            @include('content-templates::includes.content')
            @include('content-templates::includes.link')
        </article>
    </div>
</section>
