<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Helios</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=PT+Mono&display=swap" rel="stylesheet">

    {{-- Load built Vite assets from package public directory --}}
    <script type="module" crossorigin src="{{ asset('vendor/helios/assets/index-BHllPVZc.js') }}"></script>
    <link rel="stylesheet" crossorigin href="{{ asset('vendor/helios/assets/index-CyKVXIEQ.css') }}">
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div id="root"></div>
</body>
</html>