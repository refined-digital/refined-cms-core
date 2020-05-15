<ul class="nav nav__depth--{{ $parent }}">
  @foreach($data as $index => $page)
    @if ($page->id === 1) @php continue; @endphp @endif
    <li class="{{ implode(' ', $page->classes) }}">
      <a href="{!! $page->url !!}">{{ $page->name }}</a>
      @if ($page->children)
        {!!
          view()
            ->make('templates.elements.nav')
            ->with('data', $page->children)
            ->with('parent', $page->id)
        !!}
      @endif
    </li>
  @endforeach

</ul>

