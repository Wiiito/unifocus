<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0061a4">

    <title>{{ config('app.name', 'UniFocus') }} — {{ __('Plataforma Acadêmica') }}</title>

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

<body class="min-h-screen bg-background text-on-surface antialiased selection:bg-primary-container selection:text-on-primary-container">

    {{-- =========================================================================
         1. VERSÃO DESKTOP (>= lg / Telas Largas): LAYOUT ORIGINAL 100% RESTAURADO
         ========================================================================= --}}
    <div class="hidden lg:flex min-h-screen flex-col items-center justify-center px-gutter pb-lg pt-lg">
        <a href="/" class="flex flex-col items-center group transition-transform duration-normal hover:scale-105" title="{{ __('UniFocus Início') }}">
            <x-application-logo class="w-[160px] drop-shadow-sm transition-transform duration-normal group-hover:scale-105" />

            <span class="text-lg font-extrabold tracking-wide text-primary dark:text-white transition-colors duration-normal">
                UniFocus
            </span>

            <span class="text-xs font-semibold uppercase tracking-[0.08em] text-on-surface-variant">
                {{ __('Student Platform') }}
            </span>
        </a>

        <div class="mt-md w-full max-w-md overflow-hidden rounded-lg border border-card-border bg-card px-6 py-5 shadow-card transition-all duration-normal hover:shadow-card-hover">
            <div class="flex flex-col items-center gap-md text-center">
                <div>
                    <h1 class="text-2xl font-bold text-primary">
                        {{ __('Sua rotina de estudos em um só lugar') }}
                    </h1>

                    <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">
                        {{ __('Acompanhe notas, prazos e revisões diárias sem perder o foco.') }}
                    </p>
                </div>

                @auth
                    <x-ui.button :href="route('dashboard')" class="w-full">
                        <x-ui.icon name="dashboard" size="text-xl" />
                        {{ __('Ir para o dashboard') }}
                    </x-ui.button>
                @else
                    <div class="flex w-full flex-col gap-sm">
                        <x-ui.button :href="route('login')" class="w-full">
                            {{ __('Entrar') }}
                        </x-ui.button>

                        <x-ui.button variant="secondary" :href="route('register')" class="w-full">
                            {{ __('Criar conta') }}
                        </x-ui.button>
                    </div>
                @endauth
            </div>
        </div>

        {{-- Alternador de Tema discreto no Desktop --}}
        <div class="mt-6 flex items-center gap-2">
            <button type="button" x-data @click="$store.theme.toggle()"
                :aria-label="$store.theme.isDark ? '{{ __('Ativar tema claro') }}' : '{{ __('Ativar tema escuro') }}'"
                :title="$store.theme.isDark ? '{{ __('Tema claro') }}' : '{{ __('Tema escuro') }}'"
                class="flex items-center gap-2 rounded-full border border-glass bg-card/80 px-4 py-1.5 text-xs font-semibold text-on-surface-variant shadow-sm backdrop-blur-md transition-all duration-normal ease-bounce hover:border-primary/40 hover:scale-105 hover:text-primary active:scale-95">
                <span x-show="! $store.theme.isDark" class="flex items-center gap-1.5">
                    <x-ui.icon name="dark_mode" size="text-base" />
                    {{ __('Modo Escuro') }}
                </span>
                <span x-show="$store.theme.isDark" x-cloak class="flex items-center gap-1.5">
                    <x-ui.icon name="light_mode" size="text-base" />
                    {{ __('Modo Claro') }}
                </span>
            </button>
        </div>
    </div>


    {{-- =========================================================================
         2. VERSÃO MOBILE (< lg / Smartphones, Telas Estreitas e Redimensionadas):
            DESIGN BASEADO NA IMAGEM DE REFERÊNCIA
         ========================================================================= --}}
    <div class="flex lg:hidden min-h-screen flex-col justify-between">

        {{-- BANNER SUPERIOR DECORATIVO --}}
        <div class="relative w-full overflow-hidden bg-gradient-to-b from-primary to-secondary pt-safe pb-16 text-on-primary">
            {{-- Formas Orgânicas Decorativas --}}
            <div class="pointer-events-none absolute -right-12 -top-12 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -left-16 bottom-0 h-48 w-48 rounded-full bg-primary-container/20 blur-xl"></div>

            {{-- Topbar da Home Mobile com Animações de Hover --}}
            <header class="relative mx-auto flex w-full max-w-4xl items-center justify-between px-gutter pt-4">
                <a href="{{ route('subjects.index') }}" 
                   title="{{ __('Buscar matérias') }}"
                   class="flex size-11 items-center justify-center rounded-2xl bg-white/15 text-white shadow-sm backdrop-blur-md transition-all duration-normal ease-bounce hover:scale-110 hover:rotate-6 hover:bg-white/25 hover:shadow-lg active:scale-90">
                    <x-ui.icon name="search" size="text-2xl" />
                </a>

                <div class="flex items-center gap-2">
                    <x-application-logo class="w-8 brightness-0 invert" />
                    <span class="text-xl font-extrabold tracking-tight text-white">UniFocus</span>
                </div>

                <button type="button" x-data @click="$store.theme.toggle()"
                    :aria-label="$store.theme.isDark ? '{{ __('Ativar tema claro') }}' : '{{ __('Ativar tema escuro') }}'"
                    :title="$store.theme.isDark ? '{{ __('Tema claro') }}' : '{{ __('Tema escuro') }}'"
                    class="flex size-11 items-center justify-center rounded-2xl bg-white/15 text-white shadow-sm backdrop-blur-md transition-all duration-normal ease-bounce hover:scale-110 hover:rotate-12 hover:bg-white/25 hover:shadow-lg active:scale-90">
                    <x-ui.icon name="dark_mode" x-show="! $store.theme.isDark" />
                    <x-ui.icon name="light_mode" x-show="$store.theme.isDark" x-cloak />
                </button>
            </header>

            {{-- Ilustração e Destaque Visual Central da Referência --}}
            <div class="relative mx-auto mt-4 flex max-w-sm flex-col items-center justify-center px-gutter text-center">
                <div class="relative flex items-center justify-center group cursor-pointer">
                    <div class="relative flex size-28 items-center justify-center rounded-3xl bg-white/10 p-4 shadow-inner backdrop-blur-md transition-all duration-normal ease-bounce group-hover:scale-105 group-hover:bg-white/20">
                        <x-ui.icon name="school" size="text-6xl" class="text-white drop-shadow-md transition-transform duration-normal group-hover:scale-110" />
                    </div>
                    <div class="absolute -bottom-2 -right-2 flex size-10 items-center justify-center rounded-2xl bg-streak shadow-lg transition-transform duration-normal ease-bounce group-hover:scale-110 group-hover:rotate-12">
                        <x-ui.icon name="local_fire_department" filled size="text-2xl" class="text-white animate-flame-pulse" />
                    </div>
                </div>

                <span class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold tracking-wide text-white backdrop-blur-sm transition-transform duration-normal hover:scale-105">
                    <span class="size-2 rounded-full bg-streak animate-pulse"></span>
                    {{ __('Sua vida universitária em foco') }}
                </span>
            </div>
        </div>

        {{-- SHEET CARD PRINCIPAL (BORDAS SUPERIORES ARREDONDADAS rounded-t-[32px]) --}}
        <main class="relative -mt-8 flex-1 rounded-t-[32px] border-t border-card-border bg-card px-gutter pt-6 pb-28 shadow-card transition-colors duration-normal">
            <div class="mx-auto flex w-full max-w-xl flex-col gap-6">

                {{-- Título da Página com Link Interativo --}}
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-primary dark:text-white">
                            {{ __('Disciplinas') }}
                        </h2>
                        <p class="text-xs text-on-surface-variant">
                            {{ __('Exploração e recomendações do período') }}
                        </p>
                    </div>

                    <a href="{{ route('subjects.index') }}" 
                       class="inline-flex items-center gap-1 text-xs font-bold text-primary transition-all duration-fast hover:translate-x-1 hover:underline">
                        {{ __('Ver todas') }} →
                    </a>
                </div>

                {{-- Grade de Matérias (Cards Coloridos com Animação no Hover/Toque) --}}
                <div class="grid grid-cols-2 gap-3">
                    {{-- Card Matéria 1 --}}
                    <a href="{{ route('subjects.index') }}" 
                       class="group relative flex flex-col justify-between overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-primary-hover p-4 text-white shadow-md transition-all duration-normal ease-bounce hover:-translate-y-1 hover:shadow-xl hover:scale-[1.02] active:scale-95">
                        <div class="flex items-center justify-between">
                            <span class="flex size-9 items-center justify-center rounded-xl bg-white/20 text-sm font-bold backdrop-blur-sm transition-transform duration-normal group-hover:scale-110 group-hover:rotate-6">
                                <x-ui.icon name="code" size="text-lg" />
                            </span>
                            <x-ui.icon name="more_vert" size="text-lg" class="text-white/60 transition-colors group-hover:text-white" />
                        </div>
                        <div class="mt-6">
                            <span class="text-[0.65rem] font-semibold uppercase tracking-wider text-white/80">INF102</span>
                            <h3 class="text-sm font-bold leading-tight line-clamp-1">Estruturas de Dados</h3>
                        </div>
                    </a>

                    {{-- Card Matéria 2 --}}
                    <a href="{{ route('subjects.index') }}" 
                       class="group relative flex flex-col justify-between overflow-hidden rounded-2xl bg-gradient-to-br from-secondary to-secondary-container p-4 text-white shadow-md transition-all duration-normal ease-bounce hover:-translate-y-1 hover:shadow-xl hover:scale-[1.02] active:scale-95">
                        <div class="flex items-center justify-between">
                            <span class="flex size-9 items-center justify-center rounded-xl bg-white/20 text-sm font-bold backdrop-blur-sm transition-transform duration-normal group-hover:scale-110 group-hover:rotate-6">
                                <x-ui.icon name="database" size="text-lg" />
                            </span>
                            <x-ui.icon name="more_vert" size="text-lg" class="text-white/60 transition-colors group-hover:text-white" />
                        </div>
                        <div class="mt-6">
                            <span class="text-[0.65rem] font-semibold uppercase tracking-wider text-white/80">INF201</span>
                            <h3 class="text-sm font-bold leading-tight line-clamp-1">Banco de Dados</h3>
                        </div>
                    </a>
                </div>

                {{-- Seção: Agenda e Próximos Compromissos --}}
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-extrabold tracking-tight text-primary dark:text-white">
                                {{ __('Seus Horários') }}
                            </h2>
                            <p class="text-xs text-on-surface-variant">
                                {{ __('Próximas aulas e atividades programadas') }}
                            </p>
                        </div>

                        @auth
                            <a href="{{ route('agenda') }}" class="inline-flex items-center gap-1 text-xs font-bold text-primary transition-all duration-fast hover:translate-x-1 hover:underline">
                                {{ __('Agenda completa') }} →
                            </a>
                        @endauth
                    </div>

                    {{-- Card de Aula Interativo com Animação no Hover --}}
                    <div class="group relative flex items-center justify-between overflow-hidden rounded-2xl border border-glass bg-surface-container-lowest p-4 shadow-sm transition-all duration-normal ease-bounce hover:-translate-y-1 hover:border-primary/40 hover:shadow-md cursor-pointer">
                        <div class="flex items-center gap-3.5">
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-fixed text-primary font-bold transition-transform duration-normal group-hover:scale-110">
                                <x-ui.icon name="event_available" size="text-xl" />
                            </div>
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-extrabold text-primary">19:00 — 20:40</span>
                                    <span class="inline-flex items-center rounded-md bg-surface-container-high px-1.5 py-0.5 text-[0.65rem] font-medium text-on-surface-variant">Hoje</span>
                                </div>
                                <h4 class="text-sm font-bold text-on-surface">Algoritmos e Programação</h4>
                                <p class="text-xs text-on-surface-variant flex items-center gap-1 mt-0.5">
                                    <x-ui.icon name="meeting_room" size="text-[13px]" />
                                    Bloco B, sala 102
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <x-ui.icon name="chevron_right" size="text-xl" class="text-on-surface-variant/60 transition-all duration-fast group-hover:translate-x-1 group-hover:text-primary" />
                        </div>
                    </div>
                </div>

                {{-- Chamada de Ação --}}
                <div class="mt-2 rounded-2xl border border-primary/20 bg-primary-fixed/40 p-4 transition-all duration-normal hover:shadow-sm">
                    @auth
                        <div class="flex flex-col gap-3 text-center sm:flex-row sm:items-center sm:justify-between sm:text-left">
                            <div>
                                <h3 class="text-sm font-bold text-on-primary-fixed">{{ __('Sessão Ativa') }}</h3>
                                <p class="text-xs text-on-primary-fixed/80">{{ __('Acesse seu boletim, presenças e foguinho diário.') }}</p>
                            </div>
                            <x-ui.button :href="route('dashboard')" class="shrink-0 w-full sm:w-auto">
                                <x-ui.icon name="space_dashboard" size="text-lg" />
                                {{ __('Ir para o Painel') }}
                            </x-ui.button>
                        </div>
                    @else
                        <div class="flex flex-col gap-3">
                            <div class="text-center sm:text-left">
                                <h3 class="text-sm font-bold text-on-primary-fixed">{{ __('Pronto para começar?') }}</h3>
                                <p class="text-xs text-on-primary-fixed/80">{{ __('Faça login com sua conta ou crie seu cadastro em instantes.') }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <x-ui.button :href="route('login')" class="w-full text-center">
                                    {{ __('Entrar') }}
                                </x-ui.button>
                                <x-ui.button variant="secondary" :href="route('register')" class="w-full text-center">
                                    {{ __('Cadastrar') }}
                                </x-ui.button>
                            </div>
                        </div>
                    @endauth
                </div>

            </div>
        </main>

        {{-- BARRA DE NAVEGAÇÃO INFERIOR MOBILE COM DESIGN DA REFERÊNCIA (ACTIVE DOT) --}}
        @auth
            @include('layouts.partials.bottom-nav')
        @else
            <nav role="navigation" aria-label="{{ __('Navegação Principal') }}"
                class="fixed inset-x-0 bottom-0 z-50 flex items-center justify-around border-t border-glass bg-header/95 px-3
                       h-[calc(60px+env(safe-area-inset-bottom,0px))] pb-[env(safe-area-inset-bottom,0px)] shadow-[0_-8px_30px_rgba(0,97,164,0.08)] backdrop-blur-xl lg:hidden">
                <div class="mx-auto flex w-full max-w-sm items-center justify-around">
                    {{-- Início Ativo com Dot --}}
                    <a href="{{ url('/') }}" class="group relative flex flex-col items-center justify-center py-2 px-3 text-primary transition-all duration-normal ease-bounce active:scale-90" title="{{ __('Início') }}">
                        <span class="transition-transform duration-fast group-hover:-translate-y-1 group-hover:scale-110">
                            <x-ui.icon name="home" filled size="text-[1.45rem]" />
                        </span>
                        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-primary" aria-hidden="true"></span>
                        <span class="sr-only">{{ __('Início') }}</span>
                    </a>

                    {{-- Matérias --}}
                    <a href="{{ route('subjects.index') }}" class="group relative flex flex-col items-center justify-center py-2 px-3 text-on-surface-variant hover:text-primary transition-all duration-normal ease-bounce active:scale-90" title="{{ __('Matérias') }}">
                        <span class="transition-transform duration-fast group-hover:-translate-y-1 group-hover:scale-110">
                            <x-ui.icon name="menu_book" size="text-[1.45rem]" />
                        </span>
                        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-transparent group-hover:bg-primary/40 group-hover:scale-75 transition-all" aria-hidden="true"></span>
                        <span class="sr-only">{{ __('Matérias') }}</span>
                    </a>

                    {{-- Entrar / Login --}}
                    <a href="{{ route('login') }}" class="group relative flex flex-col items-center justify-center py-2 px-3 text-on-surface-variant hover:text-primary transition-all duration-normal ease-bounce active:scale-90" title="{{ __('Entrar') }}">
                        <span class="transition-transform duration-fast group-hover:-translate-y-1 group-hover:scale-110">
                            <x-ui.icon name="login" size="text-[1.45rem]" />
                        </span>
                        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-transparent group-hover:bg-primary/40 group-hover:scale-75 transition-all" aria-hidden="true"></span>
                        <span class="sr-only">{{ __('Entrar') }}</span>
                    </a>

                    {{-- Cadastrar --}}
                    <a href="{{ route('register') }}" class="group relative flex flex-col items-center justify-center py-2 px-3 text-on-surface-variant hover:text-primary transition-all duration-normal ease-bounce active:scale-90" title="{{ __('Criar Conta') }}">
                        <span class="transition-transform duration-fast group-hover:-translate-y-1 group-hover:scale-110">
                            <x-ui.icon name="person_add" size="text-[1.45rem]" />
                        </span>
                        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-transparent group-hover:bg-primary/40 group-hover:scale-75 transition-all" aria-hidden="true"></span>
                        <span class="sr-only">{{ __('Criar Conta') }}</span>
                    </a>
                </div>
            </nav>
        @endauth

    </div>

</body>

</html>
