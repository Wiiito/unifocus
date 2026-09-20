<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0061a4">

    <title>{{ $title ?? 'Painel administrativo' }} · {{ config('app.name', 'UniFocus') }}</title>

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

    {{-- Só a folha de estilos: esta área usa o Alpine embutido no bundle do
    Livewire, então o app.js (que importa e inicializa outra instância do
    Alpine) fica de fora para não colidir com ele. --}}
    @vite('resources/css/app.css')

    @livewireStyles
</head>

<body class="min-h-screen">
    <header class="border-b border-card-border bg-header">
        <div class="mx-auto flex max-w-[1100px] items-center justify-between gap-sm px-gutter py-sm md:px-md">
            <div class="flex items-center gap-sm">
                <x-application-logo class="w-[36px]" />
                <div class="flex flex-col leading-tight">
                    <span class="text-base font-extrabold tracking-wide text-primary dark:text-white">UniFocus</span>
                    <span class="text-xs font-semibold uppercase tracking-[0.08em] text-on-surface-variant">
                        {{ __('Painel administrativo') }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-sm">
                <span class="hidden text-sm text-on-surface-variant sm:inline">
                    {{ auth('admin')->user()?->name }}
                </span>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <x-ui.button type="submit" variant="secondary">
                        {{ __('Sair') }}
                    </x-ui.button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto flex w-full max-w-[1100px] flex-col gap-md px-gutter py-lg md:px-md">
        {{ $slot }}
    </main>

    @livewireScripts
</body>

</html>
