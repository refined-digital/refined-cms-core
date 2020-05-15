<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <title>{{ $page->title }}</title>
        {!! $page->head !!}
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
@if(isset($page->meta->description) && $page->meta->description)
        <meta name="description" content="{{ $page->meta->description }}"/>
@endif
        @yield('header')
        @yield('meta-description')
        @yield('facebook-og')

        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ asset('/site.webmanifest') }}">
        <link rel="mask-icon" href="{{ asset('/safari-pinned-tab.svg') }}" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="theme-color" content="#ffffff">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700&display=swap" rel="stylesheet">
        <link href="{{ asset('css/main.css?v='.uniqid()) }}" rel="stylesheet">
        @yield('styles')
    </head>

    <body{!! isset($page->classes) ? ' class="'.$page->classes.'"' : '' !!}>

    <aside class="mobile-menu">
        <header class="mobile-menu__header">
            <figure>@include('templates.includes.logo')</figure>
            <aside class="mobile-menu__close">
              <svg aria-hidden="true" focusable="false" data-prefix="far" ole="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path fill="currentColor" d="M207.6 256l107.72-107.72c6.23-6.23 6.23-16.34 0-22.58l-25.03-25.03c-6.23-6.23-16.34-6.23-22.58 0L160 208.4 52.28 100.68c-6.23-6.23-16.34-6.23-22.58 0L4.68 125.7c-6.23 6.23-6.23 16.34 0 22.58L112.4 256 4.68 363.72c-6.23 6.23-6.23 16.34 0 22.58l25.03 25.03c6.23 6.23 16.34 6.23 22.58 0L160 303.6l107.72 107.72c6.23 6.23 16.34 6.23 22.58 0l25.03-25.03c6.23-6.23 6.23-16.34 0-22.58L207.6 256z"></path></svg>
            </aside>
        </header>
        <div class="mobile-menu__inner">
            <nav>
                {!! menu()->holder(1)->view('elements.nav')->get($page) !!}
            </nav>
        </div>
    </aside>

    <div class="page__top">
      <header class="page__header">
        <figure class="page__logo">@include('templates.includes.logo')</figure>
      </header>

      @include('templates.includes.nav')
    </div>

    @include('templates.includes.banner')


