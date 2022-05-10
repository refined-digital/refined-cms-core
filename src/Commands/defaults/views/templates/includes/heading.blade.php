@if (isset($content->heading) && $content->heading)
  <h2 class="heading{{ isset($class) ? ' '.$class : '' }}">{{ $content->heading }}</h2>
@endif
