@php
    if (isset($content->vimeo_share_link)) {
      $video = help()->getVimeoEmbedLink($content->vimeo_share_link, ['muted' => true, 'controls' => 0, 'loop' => 1, 'autoplay' => 1, 'background' => 1]);
    }
@endphp
@if (isset($video) && $video)
    <section class="page__block page__block--video page__block--no-padding">
        <div class="banner__video fade-in">
            <iframe src="{{ $video }}" frameborder="0" allow="autoplay" class="video--desktop"></iframe>
        </div>
    </section>
@endif
