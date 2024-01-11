@if(isset($content->image) && $content->image->id)
    <section class="banner">
        <div class="banner__image">
            {!!
                  format()->desktopMobileImages([
                      'src' => $img->image->id,
                      'width' => $img->image->width,
                      'height' => $img->image->height
                  ],[
                      'src' => $img->mobile_image->id,
                      'width' => $img->mobile_image->width,
                      'height' => $img->mobile_image->height
                  ])
            !!}
        </div>
    </section>
@endif
