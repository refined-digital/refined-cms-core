@if (!isset($page->page_type) || (isset($page->page_type) && $page->page_type == 1))
    <url>
        <loc>{{ $page->url }}</loc>
        <lastmod>{{ $page->date }}</lastmod>
        <priority>{{ $page->priority }}</priority>
    </url>
@endif
@if(isset($page->children) && sizeof($page->children))
    @foreach($page->children as $child)
        {!! view()->make('pages::xml-sitemap.element')->with('page', $child) !!}
    @endforeach
@endif
