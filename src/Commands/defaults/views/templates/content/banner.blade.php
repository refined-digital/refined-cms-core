@if(isset($content->image) && $content->image->id)
  @php
    $image = asset(image()->load($content->image->id)->width($content->image->width)->height($content->image->height)->string());
  @endphp
  <div class="banner">
    <div class="banner__image image" style="background-image:url({{ $image }})">
      <img src="{{ asset('img/ui/banner-holder.png') }}"/>
    </div>
  </div>
@endif
