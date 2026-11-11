@php
    $user = auth()->user();
    $isDashboard = request()->routeIs('dashboard');
@endphp

<header role="banner"
    @class([
        'fixed inset-x-0 top-0 z-50 items-center justify-between gap-2 border-b border-glass bg-header px-gutter md:px-md',
        'h-[calc(68px+env(safe-area-inset-top,0px))] pt-safe backdrop-blur-lg transition-colors duration-normal ease-normal',
        'hidden md:flex' => $isDashboard,
        'flex' => ! $isDashboard,
    ])>

    <div class="flex min-w-0 items-center gap-2 sm:gap-sm">
        <a href="{{ route('profile.edit') }}" aria-label="{{ __('Perfil do Estudante') }}"
            class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-container to-primary
                   text-xl font-bold text-on-primary shadow-[0_4px_12px_rgba(0,97,164,0.25)]
                   transition duration-bounce ease-bounce active:scale-90 sm:size-11">
            {{ str($user?->name ?? 'U')->substr(0, 1)->upper() }}
        </a>

        <div class="flex min-w-0 flex-col">
            {{-- No mobile a saudação e o nome encurtam para não disputar espaço com as ações. --}}
            <span class="truncate text-xs font-medium text-on-surface-variant">
                <span class="sm:hidden">{{ __('Olá,') }}</span>
                <span class="hidden sm:inline">{{ __('Bem-vindo(a) de volta') }}</span>
            </span>
            <h1 class="truncate text-base font-bold leading-tight tracking-tight text-primary sm:text-[1.15rem]" title="{{ $user?->name }}">
                <span class="sm:hidden">{{ str($user?->name)->before(' ') }}</span>
                <span class="hidden sm:inline">{{ $user?->name }}</span>
            </h1>
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-1.5 sm:gap-sm">
        {{-- Foguinho: leva aos desafios do dia --}}
        @isset($dailyBoard)
            <a href="{{ route('dashboard') }}#desafios" class="transition duration-bounce ease-bounce active:scale-95"
                aria-label="{{ trans_choice('{0} Foguinho apagado|{1} :count dia de foguinho|[2,*] :count dias de foguinho', $dailyBoard->streakCount()) }}">
                <x-streak.flame :count="$dailyBoard->streakCount()" class="max-sm:gap-1 max-sm:px-2.5 max-sm:py-1" />
            </a>
        @endisset

        @include('layouts.partials.notification-bell')

        {{-- Alternador de tema (no mobile fica dentro do menu de configurações) --}}
        <button type="button" x-data @click="$store.theme.toggle()"
            :aria-label="$store.theme.isDark ? '{{ __('Ativar tema claro') }}' : '{{ __('Ativar tema escuro') }}'"
            :title="$store.theme.isDark ? '{{ __('Tema claro') }}' : '{{ __('Tema escuro') }}'"
            class="hidden size-[42px] items-center justify-center rounded-full bg-surface-container-low text-primary
                   transition duration-fast hover:bg-surface-container active:scale-90 sm:flex">
            <x-ui.icon name="dark_mode" x-show="! $store.theme.isDark" />
            <x-ui.icon name="light_mode" x-show="$store.theme.isDark" x-cloak />
        </button>

        {{-- Configurações da conta --}}
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button type="button" aria-label="{{ __('Configurações') }}" title="{{ __('Configurações') }}"
                    class="flex size-10 items-center justify-center rounded-full bg-surface-container-low text-primary
                           transition duration-fast hover:bg-surface-container active:scale-90 sm:size-[42px]">
                    <x-ui.icon name="settings" />
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">{{ __('Perfil') }}</x-dropdown-link>

                <button type="button" x-data @click="$store.theme.toggle()"
                    class="block w-full px-4 py-2 text-start text-sm leading-5 text-on-surface-variant transition duration-fast
                           hover:bg-surface-container-low hover:text-primary focus:bg-surface-container-low focus:outline-none sm:hidden">
                    <span x-show="! $store.theme.isDark">{{ __('Tema escuro') }}</span>
                    <span x-show="$store.theme.isDark" x-cloak>{{ __('Tema claro') }}</span>
                </button>

                {{-- Só aparece quando o navegador oferece a instalação (evento beforeinstallprompt). --}}
                <x-dropdown-link href="#" x-data x-show="$store.pwa.canInstall" x-cloak
                    @click.prevent="$store.pwa.install()">{{ __('Instalar o app') }}</x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Sair') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
