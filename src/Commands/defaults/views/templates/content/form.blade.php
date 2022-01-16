@php
  $classes = [
    'page__block',
  ];
@endphp
<div class="{{ implode(' ', $classes) }}">
  <article>
    @if (isset($content->heading) && $content->heading)
      <h2 class="heading">{{ $content->heading }}</h2>
    @endif
    <div>
      <div>{!! $content->content !!}</div>
      <div>
        @if ($content->form)
          {!! forms()->form($content->form)->render() !!}
        @endif
      </div>
    </div>
  </article>
</div>
