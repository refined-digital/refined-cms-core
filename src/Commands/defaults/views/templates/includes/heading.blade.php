@if (isset($content->heading) && $content->heading)
  <h2 class="heading{{ isset($class) ? ' '.$class : '' }}">{!! format()->heading($content->heading) !!}</h2>
@endif
