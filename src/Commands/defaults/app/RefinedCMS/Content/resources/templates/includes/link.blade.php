@if (isset($content->link) && $content->link)
    @php
        $link = format()->linkAttributes((object) $content->link);
        $classes = [
            'button',
        ];

        if (isset($link->classes) && is_array($link->classes) && sizeof($link->classes)) {
            $classes = array_merge($classes, $link->classes);
        }
    @endphp

    @if ($link->link)
        <div class="content-link">
            <a href="{{ $link->link }}"{!! $link->attributes ? ' '.$link->attributes : '' !!}{!! $classes ? ' class="'.implode($classes).'"' : '' !!}>{{ $link->title }}</a>
        </div>
    @endif
@endif
