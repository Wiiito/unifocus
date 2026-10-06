<nav role="navigation" aria-label="{{ __('Menu Mobile') }}"
    class="fixed inset-x-0 bottom-0 z-50 flex items-center justify-around gap-1 border-t border-glass bg-header px-sm
           h-[calc(64px+env(safe-area-inset-bottom,0px))] pb-safe shadow-[0_-8px_24px_rgba(0,0,0,0.06)] backdrop-blur-lg md:hidden">

    <x-ui.bottom-nav-link :href="route('dashboard')" icon="home" :active="request()->routeIs('dashboard')">
        {{ __('Início') }}
    </x-ui.bottom-nav-link>

    <x-ui.bottom-nav-link :href="route('enrollments.index')" icon="menu_book" :active="request()->routeIs('enrollments.*')">
        {{ __('Matérias') }}
    </x-ui.bottom-nav-link>

    <x-ui.bottom-nav-link :href="route('agenda')" icon="calendar_month" :active="request()->routeIs('agenda')">
        {{ __('Agenda') }}
    </x-ui.bottom-nav-link>

    <x-ui.bottom-nav-link :href="route('report-card')" icon="description" :active="request()->routeIs('report-card')">
        {{ __('Boletim') }}
    </x-ui.bottom-nav-link>

    <x-ui.bottom-nav-link :href="route('practice.show')" icon="psychology" :active="request()->routeIs('practice.*')">
        {{ __('Praticar') }}
    </x-ui.bottom-nav-link>
</nav>
