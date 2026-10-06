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
    @php
        $navigation = [
            ['route' => 'admin.dashboard', 'icon' => 'monitoring', 'label' => __('Visão geral')],
            ['route' => 'admin.institutions.index', 'icon' => 'account_balance', 'label' => __('Instituições')],
            ['route' => 'admin.academic-terms.index', 'icon' => 'date_range', 'label' => __('Períodos letivos')],
            ['route' => 'admin.subjects.index', 'icon' => 'menu_book', 'label' => __('Matérias')],
            ['route' => 'admin.questions.index', 'icon' => 'quiz', 'label' => __('Banco de questões')],
            ['route' => 'admin.users.index', 'icon' => 'group', 'label' => __('Estudantes')],
        ];
    @endphp

    <header class="sticky top-0 z-40 border-b border-glass bg-header backdrop-blur-lg">
        <div class="mx-auto flex max-w-[1320px] items-center justify-between gap-sm px-gutter py-sm md:px-md">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-sm">
                <x-application-logo class="w-[36px]" />
                <div class="flex flex-col leading-tight">
                    <span class="text-base font-extrabold tracking-wide text-primary dark:text-white">UniFocus</span>
                    <span class="text-xs font-semibold uppercase tracking-[0.08em] text-on-surface-variant">
                        {{ __('Painel administrativo') }}
                    </span>
                </div>
            </a>

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

        {{-- Navegação mobile: rolagem horizontal abaixo do cabeçalho. --}}
        <nav class="flex gap-1.5 overflow-x-auto px-gutter pb-sm md:hidden" aria-label="{{ __('Navegação do painel') }}">
            @foreach ($navigation as $item)
                <a href="{{ route($item['route']) }}" @class([
                    'flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-semibold',
                    'bg-primary-container text-on-primary-container' => request()->routeIs($item['route']),
                    'bg-surface-container-low text-on-surface-variant' => ! request()->routeIs($item['route']),
                ])>
                    <x-ui.icon :name="$item['icon']" size="text-lg" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </header>

    <div class="mx-auto grid w-full max-w-[1320px] grid-cols-1 gap-md px-gutter py-lg md:grid-cols-[230px_1fr] md:px-md">
        <aside class="sticky top-[96px] hidden h-fit md:block" aria-label="{{ __('Navegação do painel') }}">
            <x-ui.card as="nav" :inner="false" class="flex flex-col gap-1.5 p-sm">
                @foreach ($navigation as $item)
                    <x-ui.sidebar-link :href="route($item['route'])" :icon="$item['icon']" :active="request()->routeIs($item['route'])">
                        {{ $item['label'] }}
                    </x-ui.sidebar-link>
                @endforeach
            </x-ui.card>
        </aside>

        <main class="flex min-w-0 flex-col gap-md">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>

</html>
