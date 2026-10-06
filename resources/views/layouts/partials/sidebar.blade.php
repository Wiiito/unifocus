<aside aria-label="{{ __('Navegação Lateral') }}"
    class="sticky top-[calc(88px+env(safe-area-inset-top,0px))] hidden h-[calc(100vh-110px)] flex-col gap-md md:flex">

    <x-ui.card as="div" :inner="false" class="flex h-full flex-col gap-md px-sm py-md">
        {{-- Marca --}}
        <div class="flex flex-col items-center border-b border-surface-variant pb-sm text-center">
            <x-application-logo class="mb-2 w-[140px]" />
            <span class="text-[1.1rem] font-extrabold tracking-wide text-primary dark:text-white">UniFocus</span>
            <span class="text-[0.72rem] font-semibold uppercase tracking-[0.08em] text-on-surface-variant">
                {{ __('Student Platform') }}
            </span>
        </div>

        {{-- Navegação --}}
        <nav class="flex flex-col gap-1.5" role="navigation">
            <x-ui.sidebar-link :href="route('dashboard')" icon="home" :active="request()->routeIs('dashboard')">
                {{ __('Início') }}
            </x-ui.sidebar-link>

            <x-ui.sidebar-link :href="route('enrollments.index')" icon="menu_book" :active="request()->routeIs('enrollments.*')">
                {{ __('Matérias') }}
            </x-ui.sidebar-link>

            <x-ui.sidebar-link :href="route('agenda')" icon="calendar_month" :active="request()->routeIs('agenda')">
                {{ __('Agenda') }}
            </x-ui.sidebar-link>

            <x-ui.sidebar-link :href="route('report-card')" icon="description" :active="request()->routeIs('report-card')">
                {{ __('Boletim') }}
            </x-ui.sidebar-link>

            <x-ui.sidebar-link :href="route('practice.show')" icon="psychology" :active="request()->routeIs('practice.*')">
                {{ __('Praticar') }}
            </x-ui.sidebar-link>

            <x-ui.sidebar-link href="#mensagens" icon="chat_bubble">{{ __('Mensagens') }}</x-ui.sidebar-link>
        </nav>

        {{-- Meta diária: mesmo quadro do dashboard (StreakService) --}}
        @isset($dailyBoard)
            <a href="{{ route('dashboard') }}#desafios" class="mt-auto block rounded-md border border-surface-variant bg-surface-container-low p-sm transition duration-fast hover:bg-surface-container">
                <div class="mb-1.5 flex items-center justify-between text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                    <span>{{ __('Meta Diária') }}</span>
                    <span>{{ $dailyBoard->percentage() }}%</span>
                </div>

                <x-ui.progress class="mb-1.5 !h-1.5" :value="$dailyBoard->percentage()"
                    :variant="$dailyBoard->isComplete() ? 'success' : 'primary'" :label="__('Meta Diária')" />

                <p class="text-xs text-on-surface-variant">
                    {{ $dailyBoard->isComplete() ? __('Foguinho garantido hoje!') : __('Conclua seus desafios diários') }}
                </p>
            </a>
        @endisset
    </x-ui.card>
</aside>
