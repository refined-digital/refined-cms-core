@extends('core::layouts.master')

@section('title', $heading)

@section('template')

<div class="app__content">


    <div class="form">
        {!!
            html()
                ->modelForm($data, 'POST', $routes->store)
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
