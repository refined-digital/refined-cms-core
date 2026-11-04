<?php
    if (!isset($showHeader)) {
        $showHeader = true;
    }
    $search = ['[colour]','[/colour]', '|'];
    $replace = ['', '', ''];
?>
@if(isset($routeEnd) && ($routeEnd == 'index' || $routeEnd == 'fields'))
    @if($showHeader)
        <div class="app__content-header">
            <h2>
                @if (isset($parent->name))
                    <a href="{{ $parent->index ?? '#' }}">{{ str_replace($search, $replace, $parent->name) }}</a> /
                @endif
                <a href="{{ $routes->index ?? '#' }}">{{ str_replace($search, $replace, $heading) }}</a>
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
                    <a href="{{ $parent->index ?? '#' }}">{{ str_replace($search, $replace, $parent->name) }}</a> /
                @endif
                <a href="{{ $routes->index ?? '#' }}">{{ str_replace($search, $replace, $heading) }}</a> / {{ $routeEnd == 'create' ? 'Create' : ''}}
                <span>{{ str_replace($search, $replace, $data->name) }}</span>
            </h2>
            @include('core::includes.body-header-buttons')
        </div>
    @endif
    @if (isset($data) && sizeof($data->getFormFields()) > 1)
        <nav class="tab__nav">
            <ul>
                @foreach($data->getFormFields() as $tab)
                    <li
                      @click="tab = '{{ Str::slug($tab->name) }}'"
                      :class="{ 'tabs__nav-item--active' : tab == '{{ Str::slug($tab->name) }}'}"
                      data-tab="{{ Str::slug($tab->name) }}"
                      @if (isset($tab->showWhen->field))
                        data-show-when-field="{{ $tab->showWhen->field }}"
                        data-show-when-value="{{ $tab->showWhen->value }}"
                        v-show="tabVisible(@js((string) $tab->showWhen->field), @js((string) $tab->showWhen->value))"
                      @endif
                      {!! isset($tab->attrs) ? ' '.core()->objToAttr($tab->attrs) : '' !!}
                    >{{ $tab->name }}</li>
                @endforeach
            </ul>
        </nav>
    @endif
@endif
