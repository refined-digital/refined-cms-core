@extends('core::layouts.master')

@section('title', $heading)

@section('template')
    <rd-media :max-filesize="{{ help()->getUploadMaxFilesize() }}"></rd-media>
@stop
