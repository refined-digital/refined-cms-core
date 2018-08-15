<aside>
    @if (sizeof($buttons))
        @foreach ($buttons as $button)
            <a href="{{ $button->href }}" class="{{ $button->class }}"{!! ($button->href == '#' ? ' @click.prevent.stop="submitForm(\''.strtolower($button->name).'\')"' : '') !!}>{{ $button->name }}</a>
        @endforeach
    @endif
    <a href="{{ $routes->index }}" class="button button--red">Cancel</a>
</aside>