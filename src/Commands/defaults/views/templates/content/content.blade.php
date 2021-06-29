<div class="page__block">
  <div class="holder">
    <article>
      @if ($content->heading)
        <h2 class="heading">{{ $content->heading }}</h2>
      @endif
      @if ($content->content)
        <div class="cont">{!! $content->content !!}</div>
      @endif
    </article>
  </div>
</div>
