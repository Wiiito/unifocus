<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0061a4">

    <title>{{ $title ?? config('app.name', 'UniFocus') }}</title>

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
    <header class="border-b border-card-border bg-header">
        <div class="mx-auto flex max-w-[960px] items-center justify-between gap-sm px-gutter py-sm md:px-md">
            <a href="{{ route('subjects.index') }}" class="flex items-center gap-sm">
                <x-application-logo class="w-[36px]" />
                <span class="text-base font-extrabold tracking-wide text-primary dark:text-white">UniFocus</span>
            </a>

            @auth
                <x-ui.button :href="route('dashboard')" variant="secondary">
                    {{ __('Ir para o dashboard') }}
                </x-ui.button>
            @else
                <x-ui.button :href="route('login')" variant="secondary">
                    {{ __('Entrar') }}
                </x-ui.button>
            @endauth
        </div>
    </header>

    <main class="mx-auto flex w-full max-w-[960px] flex-col gap-md px-gutter py-lg md:px-md">
        {{ $slot }}
    </main>
</body>

</html>
