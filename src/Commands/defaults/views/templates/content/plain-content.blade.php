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
            @include('templates.content.includes.title')
            @include('templates.content.includes.heading')
            @include('templates.content.includes.text')
        </article>
    </div>
</section>
