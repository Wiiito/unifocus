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

<body>
    <div class="flex min-h-screen flex-col items-center px-gutter pb-lg pt-lg sm:justify-center">
        <a href="/" class="flex flex-col items-center">
            <x-application-logo class="w-[160px]" />

            <span class="text-lg font-extrabold tracking-wide text-primary dark:text-white">UniFocus</span>

            <span class="text-xs font-semibold uppercase tracking-[0.08em] text-on-surface-variant">
                {{ __('Student Platform') }}
            </span>
        </a>

        <div class="mt-md w-full overflow-hidden rounded-lg border border-card-border bg-card px-6 py-5 shadow-card sm:max-w-md">
            {{ $slot }}
        </div>
    </div>
</body>

</html>
