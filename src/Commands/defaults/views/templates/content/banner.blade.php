@if(isset($content->image) && $content->image->id)
  <section class="banner">
    <div class="banner__image">
      {!!
        image()
          ->load($image->image->id)
          ->dimensions([
              ['media' => 800, 'width' => $image->image->width, 'height' => $image->image->height],
              ['width' => $image->image->width * 0.75, 'height' => $image->image->height * 0.75]
          ])
          ->pictureHtml()
      !!}
    </div>
  </section>
@endif
