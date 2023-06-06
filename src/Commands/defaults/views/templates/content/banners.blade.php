@php
  $images = [];
  if (isset($content->images) && is_array($content->images) && sizeof($content->images)) {
    foreach ($content->images as $img) {
        if (isset($img->image) && $img->image->id) {
            $image = new stdClass();
            $image->src = desktopMobileImages([
                'src' => $img->image->id,
                'width' => $img->image->width,
                'height' => $img->image->height
            ],[
                'src' => $img->mobile_image->id,
                'width' => $img->mobile_image->width,
                'height' => $img->mobile_image->height
            ]);
          $images[] = $image;
        }
    }
  }
@endphp
@if (isset($images) && sizeof($images))
  <section class="banner banners fade-in">
    <div class="full-width-images" id="swiper--{{ uniqid() }}">
      <div class="swiper">
        <div class="swiper-wrapper">
          @foreach ($images as $img)
            <div class="swiper-slide">
              {!! $img->src !!}
            </div>
          @endforeach
        </div>
        @if (sizeof($images) > 1)
          <div class="swiper--prev"></div>
          <div class="swiper--next"></div>
        @endif
      </div>
      @if (isset($content->link) && $content->link)
        <div class="banner__caption text--right">
          @include('templates.includes.link')
        </div>
      @endif
      @if (sizeof($images) > 1)
        <div class="swiper-pagination"></div>
      @endif
    </div>
  </section>
@endif
