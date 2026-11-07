@if($meta)
    <title>{{ $meta->title }}</title>
    <meta name="description" content="{{ $meta->description }}">
    <link rel="canonical" href="{{ $meta->url }}">
    @unless($meta->indexable)
        <meta name="robots" content="noindex">
    @endunless
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="{{ $meta->locale }}">
    <meta property="og:type" content="{{ $meta->type }}">
    <meta property="og:url" content="{{ $meta->url }}">
    <meta property="og:title" content="{{ $meta->title }}">
    <meta property="og:description" content="{{ $meta->description }}">
    <meta property="og:image" content="{{ $meta->image }}">
    <meta property="og:image:type" content="{{ $meta->imageType }}">
    @if($meta->imageWidth && $meta->imageHeight)
        <meta property="og:image:width" content="{{ $meta->imageWidth }}">
        <meta property="og:image:height" content="{{ $meta->imageHeight }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    @if($meta->structuredData)
        <script type="application/ld+json">{!! $meta->structuredDataJson() !!}</script>
    @endif
@else
    <title>{{ config('app.name') }}</title>
    <meta name="robots" content="noindex">
@endif
