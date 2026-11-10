<nav role="navigation" aria-label="{{ __('Menu Mobile') }}"
    class="fixed inset-x-0 bottom-0 z-50 flex items-center justify-around border-t border-glass bg-header/95 px-2
           h-[calc(60px+env(safe-area-inset-bottom,0px))] pb-[env(safe-area-inset-bottom,0px)] shadow-[0_-8px_30px_rgba(0,97,164,0.08)] backdrop-blur-xl md:hidden">

    <div class="mx-auto flex w-full max-w-lg items-center justify-between">
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

        <x-ui.bottom-nav-link :href="route('friends.index')" icon="group" :active="request()->routeIs('friends.*')">
            {{ __('Amigos') }}
        </x-ui.bottom-nav-link>

        <x-ui.bottom-nav-link :href="route('messages.index')" icon="chat_bubble" :active="request()->routeIs('messages.*')">
            {{ __('Chat') }}
        </x-ui.bottom-nav-link>
    </div>
</nav>
