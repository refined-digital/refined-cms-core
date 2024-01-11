@if (isset($content->title) && $content->title)
    <h3 class="title{{ isset($class) ? ' '.$class : '' }}">{!! format()->heading($content->title) !!}</h3>
@endif
