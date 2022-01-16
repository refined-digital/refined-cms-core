@if (isset($images) && sizeof($images))
  <div class="banners splide" id="banner-{{ uniqid() }}">
    <div class="splide__track">
      <ul class="splide__list">
        @foreach ($images as $image)
          <li class="splide__slide">
            <figure class="banner__image image" style="background-image:url({!! $image->src !!})" data-mobile="{{ $image->mobile }}" data-desktop="{{ $image->src }}"></figure>
          </li>
        @endforeach
      </ul>
    </div>
  </div>


  @if (isset($controls) && $controls)
  <div class="banner__controls">
    <div class="splide__arrows">
      <button class="splide__arrow splide__arrow--prev" type="button" aria-label="Go to last slide">
        @include('icons.arrow-left')
      </button>
      <button class="splide__arrow splide__arrow--next" type="button" aria-label="Next slide">
        @include('icons.arrow-right')
      </button>
    </div>
  </div>
  @endif

  @if(isset($pager) && $pager)
  <div class="banner__pager"></div>
  @endif
</div>
@endif
