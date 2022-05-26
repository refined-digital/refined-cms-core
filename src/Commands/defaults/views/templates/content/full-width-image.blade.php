@php
  $classes = [
    'page__block',
    'page__block--full-width-image',
    'page__block--no-padding',
  ];
@endphp
@if (isset($content->image) && $content->image)
  <section class="{{ implode(' ', $classes) }}">
    {!!
      image()
        ->load($content->image->id)
        ->fit()
        ->dimensions([
            ['media' => 800, 'width' => $content->image->width, 'height' => $content->image->height],
            ['width' => $content->image->width * 0.75, 'height' => $content->image->height * 0.75]
        ])
        ->pictureHtml()
    !!}
  </section>
@endif
