@foreach($data as $index => $page)
  <span> | </span>
  <a href="{!! $page->url !!}">{{ $page->name }}</a>
@endforeach
