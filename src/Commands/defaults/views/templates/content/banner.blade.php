@if(isset($content->image) && $content->image->id)
  @php
    $image = asset(image()->load($content->image->id)->width($content->image->width)->height($content->image->height)->string());
    $alt = $content->image->alt ?? $content->image->fileAlt;
  @endphp
  <div class="banner">
    <div class="banner__image image" style="background-image:url({{ $image }})">
      <img src="{{ asset('img/ui/banner-holder.png') }}"{{ $alt ? ' alt="'.$alt.'"' : '' }}"/>
    </div>
  </div>
@endif
