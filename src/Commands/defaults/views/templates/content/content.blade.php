@php
    $classes = [
      'page__block',
      'page__block--content',
      'page__block--bg-'.((isset($content->background_colour) && $content->background_colour) ? $content->background_colour : 'white'),
    ];
@endphp
<section class="{{ implode(' ', $classes) }}">
    <div class="holder">
        <article class="holder__body">
            @include('templates.content.includes.heading')
            @include('templates.content.includes.content')
            @include('templates.content.includes.link')
        </article>
    </div>
</section>
