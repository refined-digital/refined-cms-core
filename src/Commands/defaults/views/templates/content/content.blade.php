@php
  $classes = [
    'page__block',
    'page__block--content',
  ];
@endphp
<section class="{{ implode(' ', $classes) }}">
  <div class="holder">
    <article>
      @include('templates.includes.heading')
      @include('templates.includes.content-text')
    </article>
  </div>
</section>
