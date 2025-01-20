@extends('core::layouts.master')

@section('title', $heading)

@section('template')

<div class="app__content">

    <div class="block">

        @if ($data->count())
            @if (isset($tableSettings->fields))
                <div class="block__controls text--right">
                    @if (isset($routes->bulk))
                        <div :class="bulk.length ? '' : 'hidden'">
                            <form method="post" action="{{ $routes->bulk }}">
                                {{ csrf_field() }}
                                {{ method_field('POST') }}

                                @foreach($data as $d)
                                    <input type="checkbox" v-model="bulk" name="bulk[]" value="{{ $d->id }}" id="item--{{ $d->id }}"/>
                                @endforeach

                                <select name="type" class="form__control" @change="handleBulk" v-model="bulkAction">
                                    <option :value="false">Select</option>
                                    <option value="Activate">Activate</option>
                                    <option value="Deactivate">Deactivate</option>
                                    @if ($canDelete)
                                    <option name="Delete">Delete</option>
                                    @endif
                                </select>
                            </form>

                            @if ($sortable && $showEnableSorting)
                                <span> | </span>
                            @endif
                        </div>
                    @endif

                    @if ($sortable && $showEnableSorting)
                        <div class="sort-button">
                            @if (request()->has('perPage') && request()->get('perPage') == 'all')
                                <a href="{{ $routes->index }}" class="button button--small button--red">Cancel Sorting</a>
                            @else
                                <a href="{{ $routes->index }}?perPage=all" class="button button--small button--green">Enable Sorting</a>
                            @endif
                        </div>
                   @endif
                </div>

                <div class="data-table">
                  @if (isset($tableSettings->note) && $tableSettings->note)
                    <p>{!! $tableSettings->note !!}</p>
                  @endif

                    <table{!! $sort ? ' v-sortable-table data-route="'.$routes->sort.'"' : '' !!}>
                        <thead>
                        <tr>
                            @if ($sort)
                                <th class="data-table__cell data-table__cell--sort"></th>
                            @endif
                            @if(isset($routes->bulk))
                            <th class="data-table__cell data-table__cell--bulk"></th>
                            @endif
                            @foreach($tableSettings->fields as $field)
                                <th class="data-table__cell{{ isset($field->classes) ? ' '.implode(' ', $field->classes) : '' }}">{!! help()->getTableLink($field) !!}</th>
                            @endforeach
                            <th></th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($data as $d)
                            <tr data-id="{{ $d->id }}">
                                @if ($sort)
                                    <td class="data-table__cell data-table__cell--sort"><i class="fa fa-sort"></i></td>
                                @endif
                                @if (isset($routes->bulk))
                                <td class="data-table__cell data-table__cell--bulk">
                                    <div class="form__checkbox form__checkbox--no-input" :class="
                                        bulk.includes('{{ $d->id }}') ? 'checked' : ''
                                    ">
                                        <label for="item--{{ $d->id }}"></label>
                                    </div>
                                </td>
                                @endif
                                @foreach($tableSettings->fields as $field)
                                    <td class="data-table__cell{{ isset($field->classes) ? ' '.implode(' ', $field->classes) : '' }}">
                                        @if (isset($tableSettings->routes->edit) || isset($field->route))
                                            @if (isset($field->route))
                                                <a href="{{ route($field->route, $d->id) }}">
                                            @else
                                                <a href="{{ route($tableSettings->routes->edit, $d->id) }}">
                                            @endif
                                        @endif

                                            @if (isset($field->type) && view()->exists('core::includes.index.'.$field->type))
                                                @include('core::includes.index.'.$field->type)
                                            @else
                                                @include('core::includes.index.default')
                                            @endif

                                        @if (isset($tableSettings->routes->edit))
                                            </a>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="data-table__cell data-table__cell--controls">
                                    @if (isset($tableSettings->routes->edit))
                                        <a href="{{ route($tableSettings->routes->edit, $d->id) }}" title="Edit"><i class="fa fa-pencil-alt"></i></a>
                                    @endif

                                    @if (isset($tableSettings->extraActions) && is_array($tableSettings->extraActions) && sizeof($tableSettings->extraActions))
                                        @foreach ($tableSettings->extraActions as $action)
                                            <a href="{{ route($action->route, $d->id) }}" title="{{ $action->name }}"><i class="{{ $action->icon }}"></i></a>
                                        @endforeach
                                    @endif

                                    @if ($canDelete && isset($tableSettings->routes->destroy) && !in_array($d->id, $tableSettings->noDelete))
                                        <form method="post" action="{{ route($tableSettings->routes->destroy, $d->id) }}" v-confirm-delete>
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                            <button title="Delete"><i class="fa fa-trash-alt"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                    </table>

                    @include('core::includes.paging')
                </div>

            @else
                @include('core::includes.no-table-settings')
            @endif

        @else
            @include('core::includes.no-results')
        @endif
    </div>

</div>
@stop
