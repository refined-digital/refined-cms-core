@php
    $classes = [
      'page__block',
      'page__block--plain-content',
      'page__block--bg-'.((isset($content->background_colour) && $content->background_colour) ? $content->background_colour : 'white'),
    ];
@endphp
<section class="{{ implode(' ', $classes) }}">
    <div class="holder holder--large">
        <article>
            @include('templates.includes.title')
            @include('templates.includes.heading')
            @include('templates.includes.content-text')
        </article>
    </div>
</section>
