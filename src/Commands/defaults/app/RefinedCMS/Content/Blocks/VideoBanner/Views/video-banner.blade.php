@php
    $classes = array_merge($classes, [
      'page__block--no-padding',
    ]);

    if (isset($content->video->id) && $content->video->id) {
        $video = files()->load($content->video->id)->url();
    }

@endphp
@if (isset($video) && $video)
    <section class="banner banner--video {{ implode(' ', $classes) }}">
        <video
                class="banner__video"
                autoplay
                muted
                loop
                playsinline
                webkit-playsinline
                x-webkit-airplay="deny"
                preload="auto"
                disablePictureInPicture
        >
            <source src="{{ $video }}" type="video/mp4">
        </video>
    </section>
@endif
