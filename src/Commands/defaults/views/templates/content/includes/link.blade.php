@if (isset($content->link) && $content->link)
    <div class="content-link">
        <a href="{{ $content->link }}">
            {{ $content->link_text ?? 'Read more' }}
        </a>
    </div>
@endif
