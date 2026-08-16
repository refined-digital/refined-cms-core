@if (isset($content->title) && $content->title)
    {{-- a kicker above the block heading, and the published date on news
         details, so not a section title. a heading tag here sits above the
         h2 it introduces and inverts the outline --}}
    <p class="title{{ isset($class) ? ' '.$class : '' }}" data-field="title">{!! format()->heading($content->title) !!}</p>
@endif
