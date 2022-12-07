@if(isset($content->image) && $content->image->id)
  <section class="banner">
    <div class="banner__image">
      {!!
        image()
          ->load($content->image->id)
          ->dimensions([
              ['media' => 800, 'width' => $content->image->width, 'height' => $content->image->height],
              ['width' => $content->image->width * 0.75, 'height' => $content->image->height * 0.75]
          ])
          ->pictureHtml()
      !!}
    </div>
  </section>
@endif
