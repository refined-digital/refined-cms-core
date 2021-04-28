
    <url>
        <loc>{{ $page->url }}</loc>
        <lastmod>{{ $page->date }}</lastmod>
        <priority>{{ $page->priority }}</priority>
    </url>
@if(isset($page->children) && sizeof($page->children))
    @foreach($page->children as $child)
        {!! view()->make('pages::xml-sitemap.element')->with('page', $child) !!}
    @endforeach
@endif
