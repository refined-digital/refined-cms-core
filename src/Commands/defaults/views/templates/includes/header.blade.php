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

    <link rel="icon" type="image/png" href="{{ asset('/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/apple-touch-icon.png') }}" />
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}l" />
    <link rel="manifest" href="{{ asset('/site.webmanifest') }}" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/main.css'])
    @vite(app(\RefinedDigital\CMS\Modules\Core\Aggregates\AssetAggregate::class)->getStyles())
    @yield('styles')

    @vite(['resources/js/main.js'])
    @vite(app(\RefinedDigital\CMS\Modules\Core\Aggregates\AssetAggregate::class)->getScripts())
</head>

<body{!! isset($page->classes) ? ' class="'.$page->classes.'"' : '' !!}>

@include('templates.modals.mobile-menu')
@include('templates.modals.enquire')

@include('templates.includes.top')
