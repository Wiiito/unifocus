@php
    $user = auth()->user();
@endphp

<header role="banner"
    class="fixed inset-x-0 top-0 z-50 flex items-center justify-between gap-sm border-b border-glass bg-header px-gutter md:px-md
           h-[calc(68px+env(safe-area-inset-top,0px))] pt-safe backdrop-blur-lg transition-colors duration-normal ease-normal">

    <div class="flex min-w-0 items-center gap-sm">
        <a href="{{ route('profile.edit') }}" aria-label="{{ __('Perfil do Estudante') }}"
            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-container to-primary
                   text-xl font-bold text-on-primary shadow-[0_4px_12px_rgba(0,97,164,0.25)]
                   transition duration-bounce ease-bounce active:scale-90">
            {{ str($user?->name ?? 'U')->substr(0, 1)->upper() }}
        </a>

        <div class="flex min-w-0 flex-col">
            <span class="text-xs font-medium text-on-surface-variant">{{ __('Bem-vindo(a) de volta') }}</span>
            <h1 class="truncate text-[1.15rem] font-bold tracking-tight text-primary">{{ $user?->name }}</h1>
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-sm">
        {{-- Foguinho: leva aos desafios do dia --}}
        @isset($dailyBoard)
            <a href="{{ route('dashboard') }}#desafios" class="transition duration-bounce ease-bounce active:scale-95"
                aria-label="{{ trans_choice('{0} Foguinho apagado|{1} :count dia de foguinho|[2,*] :count dias de foguinho', $dailyBoard->streakCount()) }}">
                <x-streak.flame :count="$dailyBoard->streakCount()" />
            </a>
        @endisset

        {{-- Alternador de tema --}}
        <button type="button" x-data @click="$store.theme.toggle()"
            :aria-label="$store.theme.isDark ? '{{ __('Ativar tema claro') }}' : '{{ __('Ativar tema escuro') }}'"
            :title="$store.theme.isDark ? '{{ __('Tema claro') }}' : '{{ __('Tema escuro') }}'"
            class="flex size-[42px] items-center justify-center rounded-full bg-surface-container-low text-primary
                   transition duration-fast hover:bg-surface-container active:scale-90">
            <x-ui.icon name="dark_mode" x-show="! $store.theme.isDark" />
            <x-ui.icon name="light_mode" x-show="$store.theme.isDark" x-cloak />
        </button>

        {{-- Configurações da conta --}}
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button type="button" aria-label="{{ __('Configurações') }}" title="{{ __('Configurações') }}"
                    class="flex size-[42px] items-center justify-center rounded-full bg-surface-container-low text-primary
                           transition duration-fast hover:bg-surface-container active:scale-90">
                    <x-ui.icon name="settings" />
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">{{ __('Perfil') }}</x-dropdown-link>

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
