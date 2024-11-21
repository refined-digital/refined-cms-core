@if (isset($content->link) && $content->link)
    @php
        $link = format()->linkAttributes($content->link);
    @endphp

    @if ($link->link)
        <div class="content-link">
            <a href="{{ $link->link }}"{!! $link->attributes ? ' '.$link->attributes : '' !!}{!! $link->classes ? ' class="'.implode($link->classes).'"' : '' !!}>{{ $link->title }}</a>
        </div>
    @endif
@endif
