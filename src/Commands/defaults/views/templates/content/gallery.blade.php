@if (isset($content->images) && is_array($content->images) && sizeof($content->images))
  @php
    $images = [];
    foreach ($content->images as $img) {
        if (isset($img->image) && $img->image->id) {
          $image = new stdClass();
          $image->image = asset(image()->load($img->image->id)->width($img->image->width)->height($img->image->height)->string());
          $image->alt = $img->image->alt ?? $img->image->fileAlt;
          $images[] = $image;
        }
    }
  @endphp
@endif
@if (sizeof($images))
<div class="page__block page__block--gallery">
  <div class="holder">
    <div class="gallery">
      @foreach ($images as $image)
        <figure class="gallery__image">
          <div class="image" style="background-image:url({{ $image->image }})">
            <img src="{{ asset('img/ui/holder.png') }}" class="image__placeholder"{!! $image->alt ? ' alt="'.$image->alt.'"' : '' !!}/>
          </div>
        </figure>
      @endforeach
    </div>
  </div>
</div>
@endif
