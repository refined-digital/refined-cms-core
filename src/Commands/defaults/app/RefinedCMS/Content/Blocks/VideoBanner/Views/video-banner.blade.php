@php
    $classes = array_merge($classes, [
      'page__block--no-padding',
    ]);

    if (isset($content->video->id) && $content->video->id) {
        $video = video()->load($content->video->id);
    }

@endphp
@if (isset($video) && $video->url())
    <section class="banner banner--video {{ implode(' ', $classes) }}">
        {!! $video->banner() !!}
    </section>
@endif
