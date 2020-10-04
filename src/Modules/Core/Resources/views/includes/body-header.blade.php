<?php
    if (!isset($showHeader)) {
        $showHeader = true;
    }
?>
@if(isset($routeEnd) && ($routeEnd == 'index' || $routeEnd == 'fields'))
    @if($showHeader)
        <div class="app__content-header">
            <h2>
                @if (isset($parent->name))
                    <a href="{{ $parent->index ?? '#' }}">{{ $parent->name }}</a> /
                @endif
                <a href="{{ $routes->index ?? '#' }}">{{ $heading }}</a>
            </h2>
            <aside>
                @if ($canCreate && isset($routes->create) && ($routeEnd == 'index' || $routeEnd == 'fields'))
                    <a href="{{ $routes->create }}" class="button button--blue">Add{{ $button ? ' '.$button : ''}}</a>
                @endif

                @if (isset($indexButtons) && sizeof($indexButtons))
                    @foreach ($indexButtons as $button)
                        <a href="{{ $button->href }}" class="{{ $button->class }}">{{ $button->name }}</a>
                    @endforeach
                @endif
            </aside>
        </div>
    @endif
@else
    @if($showHeader && isset($data))
        <div class="app__content-header">
            <h2>
                @if (isset($parent->name))
                    <a href="{{ $parent->index ?? '#' }}">{{ $parent->name }}</a> /
                @endif
                <a href="{{ $routes->index ?? '#' }}">{{ $heading }}</a> / {{ $routeEnd == 'create' ? 'Create' : ''}}
                <span>{{ $data->name }}</span>
            </h2>
            @include('core::includes.body-header-buttons')
        </div>
    @endif
    @if (isset($data) && sizeof($data->getFormFields()) > 1)
        <nav class="tab__nav">
            <ul>
                @foreach($data->getFormFields() as $tab)
                    <li
                      @click="tab = '{{ str_slug($tab->name) }}'"
                      :class="{ 'tabs__nav-item--active' : tab == '{{ str_slug($tab->name) }}'}"
                      {!! isset($tab->attrs) ? ' '.core()->objToAttr($tab->attrs) : '' !!}
                    >{{ $tab->name }}</li>
                @endforeach
            </ul>
        </nav>
    @endif
@endif
