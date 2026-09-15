<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0061a4">

    <title>{{ config('app.name', 'UniFocus') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo-unifocus.png') }}">

    {{-- Aplica o tema antes da primeira pintura, evitando flash de cor errada. --}}
    <script>
        (function () {
            var theme = 'light';
            try {
                theme = localStorage.getItem('unifocus_theme')
                    || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            } catch (e) {}
            document.documentElement.dataset.theme = theme;
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen">
    @include('layouts.partials.header')

    <div class="mx-auto grid w-full max-w-[1320px] grid-cols-1 gap-md px-gutter md:grid-cols-[220px_1fr] md:px-md lg:grid-cols-[260px_1fr]
                mt-[calc(84px+env(safe-area-inset-top,0px))] mb-[calc(84px+env(safe-area-inset-bottom,0px))] md:mb-md">
        @include('layouts.partials.sidebar')

        <main class="flex flex-col gap-md">
            @isset($header)
                <header class="flex items-center justify-between">
                    {{ $header }}
                </header>
            @endisset

            {{ $slot }}
        </main>
    </div>

    @include('layouts.partials.bottom-nav')
</body>

</html>
