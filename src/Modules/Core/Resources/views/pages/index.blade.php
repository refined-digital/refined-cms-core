@extends('core::layouts.master')

@section('title', $heading)

@section('template')

<div class="app__content">

    <div class="block">

        @if ($data->count())
            @if (isset($tableSettings->fields))
                @if ($sortable)
                    <p class="text--right">
                        @if (request()->has('perPage') && request()->get('perPage') == 'all')
                            <a href="{{ $routes->index }}" class="text--red">Cancel Sorting</a>
                        @else
                            <a href="{{ $routes->index }}?perPage=all">Enable Sorting</a>
                        @endif
                    </p>
                @endif

                <div class="data-table">

                    <table{!! $sort ? ' v-sortable-table data-route="'.$routes->sort.'"' : '' !!}>
                        <thead>
                        <tr>
                            @if ($sort)
                                <th class="data-table__cell data-table__cell--sort"></th>
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
                                @foreach($tableSettings->fields as $field)
                                    <td class="data-table__cell{{ isset($field->classes) ? ' '.implode(' ', $field->classes) : '' }}">
                                        @if (isset($tableSettings->routes->edit))
                                            <a href="{{ route($tableSettings->routes->edit, $d->id) }}">
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
                                            <a href="{{ route($action->route, $d->id) }}" title="{{ $action->name }}"><i class="fa {{ $action->icon }}"></i></a>
                                        @endforeach
                                    @endif

                                    @if (isset($tableSettings->routes->destroy) && !in_array($d->id, $tableSettings->noDelete))
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
