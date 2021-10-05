@extends('core::layouts.master')

@section('title', $heading)

@section('template')
  <rd-pages
    site-url="{{ rtrim(config('app.url'), '/') }}"
    :config="{{ json_encode(pages()->getConfig()) }}"
    :modules="{{ json_encode(pages()->getModules()) }}"
  ></rd-pages>
@stop
