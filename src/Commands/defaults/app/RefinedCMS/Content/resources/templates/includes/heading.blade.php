@if (isset($content->heading) && $content->heading)
    {{-- the first block heading on the page becomes the h1, the rest stay h2.
         pass $level to a block that sits lower in the outline --}}
    @php($tag = format()->headingTag($level ?? 'h2'))
    {{-- data-field powers the page builder's instant keystroke preview --}}
    <{{ $tag }} class="heading{{ isset($class) ? ' '.$class : '' }}" data-field="heading">{!! format()->heading($content->heading) !!}</{{ $tag }}>
@endif
