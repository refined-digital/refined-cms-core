@extends('core::layouts.master')

@section('title', $heading)

@section('template')
  <rd-workspace :config="{{ json_encode($workspaceConfig) }}"></rd-workspace>
@stop
