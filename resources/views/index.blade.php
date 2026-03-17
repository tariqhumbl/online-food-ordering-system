<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Online Ordering System</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet" />
        <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/x-icon" />
        <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/jsvectormap.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/fonts/phosphor/duotone/style.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}" />
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    </head>
    <body>
        <div id="app"></div>
        @vite('resources/js/app.js')
        {{-- <script src="{{ asset('assets/js/plugins/jsvectormap.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/apexcharts.min.j') }}"></script> --}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
