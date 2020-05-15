@php
  $config = config('page-banners.fields.image');
  $width = $config['internal']['width'];
  $height = $config['internal']['height'];
  $banners = isset($page->data->banners) ? $page->data->banners : [];
  $depth = isset($page->depth) ? $page->depth : 1;

  if (isset($config['depths'][$depth])) {
    $width = $config['depths'][$depth]['width'];
    $height = $config['depths'][$depth]['height'];
  }

  $images = [];
  if (sizeof($banners)) {
    foreach ($banners as $banner) {
      if ($banner->image->content) {
        $img = image()->load($banner->image->content)->width($width)->height($height)->string();
        $images[] = $img;
      }
    }
  }
@endphp
<div class="banner">
  <div class="banner__images{{ sizeof($images) > 1 ? ' cycle-slideshow' : '' }}"
    @if (sizeof($images) > 1)
    data-cycle-timeout="7000"
    data-cycle-speed="1400"
    data-cycle-log="false"
    data-cycle-slides=">.banner__image"
    data-cycle-swipe="true"
    @endif
  >
    @foreach ($images as $image)
      <figure class="banner__image image" style="background-image:url({{ asset($image->source) }})">
        <img src="{{ asset('img/ui/banner-'.$height.'-holder.png') }}" class="image__placeholder"/>
      </figure>
    @endforeach
  </div>
</div>
