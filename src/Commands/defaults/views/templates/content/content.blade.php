@php
    $classes = [
      'page__block',
      'page__block--content',
      'page__block--bg-'.((isset($content->background_colour) && $content->background_colour) ? $content->background_colour : 'white'),
    ];
@endphp
<section class="{{ implode(' ', $classes) }}">
    <div class="holder">
        <article>
            @include('templates.includes.content.heading')
            @include('templates.includes.content.text')
        </article>
    </div>
</section>
