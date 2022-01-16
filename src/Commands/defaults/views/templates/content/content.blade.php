<div class="page__block">
  <div class="holder">
    <article>
      @if (isset($content->heading) && $content->heading)
        <h2 class="heading">{{ $content->heading }}</h2>
      @endif
      @if (isset($content->content) && $content->content)
        <div class="cont">{!! $content->content !!}</div>
      @endif
    </article>
  </div>
</div>
