@extends('core::layouts.master')

@section('title', 'Dashboard')

@section('template')
  <div class="dashboard">
    <header class="dashboard__header">
      <h1>Welcome back{{ auth()->user() ? ', '.auth()->user()->first_name : '' }}</h1>
      <p>Where would you like to start?</p>
    </header>

    @if (is_array($tiles) && sizeof($tiles))
      <div class="dashboard__tiles">
        @foreach ($tiles as $item)
          <a
            class="dashboard__tile"
            href="{{ is_array($item->route) ? route('refined.'.$item->route[0], $item->route[1]) : route('refined.'.$item->route.'.index') }}"
          >
            <span class="dashboard__tile-icon">{!! $item->icon !!}</span>
            <span class="dashboard__tile-name">{{ $item->name }}</span>
          </a>
        @endforeach
      </div>
    @endif
  </div>
@stop
