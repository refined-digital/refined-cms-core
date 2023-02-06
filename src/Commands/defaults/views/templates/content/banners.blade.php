@php
  $images = [];
  foreach ($content->images as $img) {
      if (isset($img->image) && $img->image->id) {
          $image = new stdClass();
          $image->src = image()
            ->load($img->image->id)
            ->dimensions([
                ['media' => 800, 'width' => $img->image->width, 'height' => $img->image->height],
                ['width' => $img->image->width * 0.75, 'height' => $img->image->height * 0.75]
            ])
            ->pictureHtml();
        $images[] = $image;
      }
  }
@endphp
@if (isset($images) && sizeof($images))
  <section class="banner splide" id="banner-{{ uniqid() }}">
    <div class="splide__track">
      <ul class="splide__list">
        @foreach ($images as $image)
          <li class="splide__slide">
            <figure class="banner__image">
              {!! $image !!}
            </figure>
          </li>
        @endforeach
      </ul>
    </div>
    <div class="splide__arrows">
      <button class="splide__arrow splide__arrow--prev" type="button" aria-label="Go to last slide">
        @include('icons.arrow-left')
      </button>
      <button class="splide__arrow splide__arrow--next" type="button" aria-label="Next slide">
        @include('icons.arrow-right')
      </button>
    </div>
  </section>
  </div>
@endif


