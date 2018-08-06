@if(isset($routes->search) && $routes->search)
<div class="app__search search">
    {!! html()->form('GET', $routes->search)->open() !!}
        {!! html()
                ->text('keywords')
                ->placeholder('Search...')
                ->value(request()->get('keywords'))
                ->attribute('autocomplete', 'off')
        !!}

        @if(request()->has('keywords'))
            <a href="{{ $routes->search }}"><i class="fa fa-times"></i></a>
        @endif

        <button type="submit">
            <i class="fa fa-search"></i>
        </button>
    {!! html()->form()->close() !!}
</div>
@endif