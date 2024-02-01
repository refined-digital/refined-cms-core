<!DOCTYPE html>
<html lang="en">

    <head>
        <title>@yield('title') :: Refined CMS</title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex">

        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
        <link rel="stylesheet" href="{{ refined_asset('vendor/refined/core/css/main.css?v='.uniqid()) }}"/>
    </head>

    <body>
