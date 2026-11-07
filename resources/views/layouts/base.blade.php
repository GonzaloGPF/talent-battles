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
    @include('partials.meta', ['meta' => $meta ?? null])
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

    @vite(['resources/js/app.js'])
</head>
<body>
@include('partials.errors')

@yield('content')

@stack('scripts')
</body>
</html>
