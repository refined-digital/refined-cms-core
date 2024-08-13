@if(isset($content->image) && $content->image->id)
    <section class="banner">
        <div class="banner__image">
            {!!
                  format()->desktopMobileImages([
                      'src' => $content->image->id,
                      'width' => $content->image->width,
                      'height' => $content->image->height
                  ],[
                      'src' => $content->mobile_image->id,
                      'width' => $content->mobile_image->width,
                      'height' => $content->mobile_image->height
                  ])
            !!}
        </div>
    </section>
@endif
