<!DOCTYPE html>
<html lang="{{ isset($user) ?  $user['locale'] : app()->getLocale() }}">
<head>
    @include('partials.gtm')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="app_url" content="{{ config('app.url') }}">
    <meta name="app_name" content="{{ config('app.name') }}">
    <meta name="company_name" content="{{ config('company.name') }}">
    <meta name="company_acronym" content="{{ config('company.acronym') }}">
    <meta property="og:locale" content="es_ES" />
    <meta property="og:url" content="{{ config('app.url') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Talent Battles" />
    <meta property="og:description" content="La red social de los artistas" />
    <meta property="og:image" content="{{ url('images/logo_white_on_black.webp') }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="200">
    <meta property="og:image:height" content="200">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/favicon.svg') }}">

    <title>{{ config('app.name') }}</title>

    @vite(['resources/js/app.js'])
</head>
<body>
@include('partials.errors')

@yield('content')

@stack('scripts')
</body>
</html>
