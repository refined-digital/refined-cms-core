@extends('core::layouts.master')

@section('title', $heading)

@section('template')
  @php
    $content = app(\RefinedDigital\CMS\Modules\Core\Aggregates\ContentAggregate::class)->getForConfig();
  @endphp
  <rd-pages
    site-url="{{ rtrim(config('app.url'), '/') }}"
    public-url="{{ rtrim(env('PUBLIC_URL') ?? config('app.url'), '/') }}"
    :config="{{ json_encode(pages()->getConfig()) }}"
    :modules="{{ json_encode(pages()->getModules()) }}"
    :content="{{ json_encode($content) }}"
  ></rd-pages>
@stop
