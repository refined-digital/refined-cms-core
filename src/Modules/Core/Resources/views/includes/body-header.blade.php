<?php
    if (!isset($showHeader)) {
        $showHeader = true;
    }
?>
@if($routeEnd == 'index')
    @if($showHeader)
        <div class="app__content-header">
            <h2>
                <a href="{{ $routes->index or '#' }}">{{ $heading }}</a>
            </h2>
            <aside>
                @if (isset($routes->create) && $routeEnd == 'index')
                    <a href="{{ $routes->create }}" class="button button--blue">Add{{ $button ? ' '.$button : ''}}</a>
                @endif
            </aside>
        </div>
    @endif
@else
    @if($showHeader)
        <div class="app__content-header">
            <h2>
                <a href="{{ $routes->index or '#' }}">{{ $heading }}</a> / {{ $routeEnd == 'create' ? 'Create' : ''}}
                <span>{{ $data->name }}</span>
            </h2>
            <aside>
                <a href="#" class="button button--blue" @click.prevent.stop="submitForm('save')">Save</a>
                <a href="#" class="button button--blue" @click.prevent.stop="submitForm('save & return')">Save & Return</a>
                <a href="#" class="button button--blue" @click.prevent.stop="submitForm('save & new')">Save & New</a>
                <a href="{{ $routes->index }}" class="button button--red">Cancel</a>
            </aside>
        </div>
    @endif
    @if (sizeof($data->getFormFields()) > 1)
        <nav class="tab__nav">
            <ul>
                @foreach($data->getFormFields() as $tab)
                    <li @click="tab = '{{ str_slug($tab->name) }}'" :class="{ 'tabs__nav-item--active' : tab == '{{ str_slug($tab->name) }}'}">{{ $tab->name }}</li>
                @endforeach
            </ul>
        </nav>
    @endif
@endif