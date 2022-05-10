@php
  $classes = [
    'page__block',
    'page__block--form',
  ];
@endphp
<section class="{{ implode(' ', $classes) }}">
  <article>
    @include('templates.includes.heading')
    <div>
      @include('templates.includes.content-text')
      <div>
        @if ($content->form)
          {!! forms()->form($content->form)->render() !!}
        @endif
      </div>
    </div>
  </article>
</section>
