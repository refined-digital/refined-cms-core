@if (isset($content->image) && $content->image)
    <figure{!! isset($class) ? ' class="'.$class.'"' : '' !!}>
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
    </figure>
@endif
