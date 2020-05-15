<div class="page__block page__content">
  <div class="holder">
    <article>
      <header>
        <h1 class="heading{{ isset($blog) ? ' heading--center' : '' }}">
          {!! $page->getContentBySource('page-heading') ?: $page->name !!}
        </h1>
      </header>

      @if ($page->getContentBySource('content'))
        <div class="cont{{ isset($blog) ? ' text--center' : '' }}">
          {!! $page->getContentBySource('content') !!}
        </div>
      @endif
    </article>
  </div>
</div>
