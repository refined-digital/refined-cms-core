@if (isset($images) && sizeof($images))
<div class="banner">
  <div class="banner__images{{ sizeof($images) > 1 ? ' cycle-slideshow' : '' }}"
    @if (sizeof($images) > 1)
    data-cycle-timeout="7000"
    data-cycle-speed="1400"
    data-cycle-log="false"
    data-cycle-slides=">.banner__image"
    @if (isset($controls) && $controls)
      data-cycle-prev=">.banner__controls .banner__control--left"
      data-cycle-next=">.banner__controls .banner__control--right"
    @endif
    @if (isset($pager) && $pager)
    data-cycle-pager=">.banner__pager"
    @endif
    data-cycle-swipe="true"
    @endif
  >
    @foreach ($images as $image)
      <figure class="banner__image image" style="background-image:url({{ $image }})">
        <img src="{{ asset('img/ui/banner-holder.png') }}" class="image__placeholder"/>
      </figure>
    @endforeach
  </div>

  @if (isset($controls) && $controls)
  <div class="banner__controls">
    <div class="banner__control banner__control--left">@include('icons.arrow-left')</div>
    <div class="banner__control banner__control--right">@include('icons.arrow-right')</div>
  </div>
  @endif

  @if(isset($pager) && $pager)
  <div class="banner__pager"></div>
  @endif
</div>
@endif
