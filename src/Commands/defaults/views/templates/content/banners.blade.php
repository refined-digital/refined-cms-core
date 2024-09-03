@php
    use RefinedDigital\CMS\Modules\Core\Aggregates\AssetAggregate;
    app(AssetAggregate::class)->addStyle('banner.css');

    $images = [];
    if (isset($content->images) && is_array($content->images) && sizeof($content->images)) {
      foreach ($content->images as $img) {
          if (isset($img->image) && $img->image->id) {
              $image = new stdClass();
              $image->src = format()->desktopMobileImages([
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
        <swiper-container loop="true" navigation="true" pagination="true" pagination-clickable="true">
            @foreach ($images as $img)
                <swiper-slide>
                    {!! $img->src !!}
                </swiper-slide>
            @endforeach
        </swiper-container>
    </section>
@endif
