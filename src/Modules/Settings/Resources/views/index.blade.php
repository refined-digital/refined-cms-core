@extends('core::layouts.master')

@section('title', $heading)

@section('template')
    <rd-settings :data="{{ json_encode($data) }}" model="{{ $settingModel }}" heading="{{ $heading ?? 'Settings' }}"></rd-settings>
@stop
