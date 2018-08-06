@extends('core::layouts.master')

@section('title', $heading)

@section('template')
    <rd-pages
        site-url="{{ rtrim(config('app.url'), '/') }}"
        :config="{{ json_encode(config('page')) }}"
    ></rd-pages>
@stop
