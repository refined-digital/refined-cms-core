<aside class="mobile-menu">
  <header class="mobile-menu__header">
    <figure>@include('templates.includes.logo')</figure>
    <aside class="mobile-menu__close">
      @include('icons.close')
    </aside>
  </header>
  <div class="mobile-menu__inner">
    <nav>
      {!! menu()->holder(1)->view('elements.nav')->get($page) !!}
    </nav>
  </div>
</aside>
