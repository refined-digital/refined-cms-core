@extends('core::layouts.master')

@section('title', $heading)

@section('template')

<div class="app__content">

    <div class="form">
        {!!
            html()
                ->modelForm($data, 'PUT', route($routes->update, $data->id))
                ->attributes([
                    'id' => 'model-form',
                    'novalidate'
                ])
                ->open()
        !!}

        @include('core::pages._form')

        {!! html()->closeModelForm() !!}
    </div>

</div>
@stop
