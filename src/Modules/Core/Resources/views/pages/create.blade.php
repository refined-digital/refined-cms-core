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

        @if(view()->exists($prefix.'_form'))
            @include($prefix.'_form')
        @else
            @include('core::pages._form')
        @endif

        {!! html()->closeModelForm() !!}
    </div>

</div>
@stop
