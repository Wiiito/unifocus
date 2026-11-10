{{--
    Barra inferior do mobile. Segue o limite de 5 destinos das diretrizes de
    navegação inferior: 4 principais + "Mais", que abre uma folha com o restante.
--}}
@php
    $primaryItems = [
        ['route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'home', 'label' => __('Início')],
        ['route' => 'enrollments.index', 'active' => 'enrollments.*', 'icon' => 'menu_book', 'label' => __('Matérias')],
        ['route' => 'agenda', 'active' => 'agenda', 'icon' => 'calendar_month', 'label' => __('Agenda')],
        ['route' => 'practice.show', 'active' => 'practice.*', 'icon' => 'psychology', 'label' => __('Praticar')],
    ];

    $moreItems = [
        ['route' => 'report-card', 'active' => 'report-card', 'icon' => 'description', 'label' => __('Boletim')],
        ['route' => 'friends.index', 'active' => 'friends.*', 'icon' => 'group', 'label' => __('Amigos')],
        ['route' => 'messages.index', 'active' => 'messages.*', 'icon' => 'chat_bubble', 'label' => __('Mensagens')],
    ];

    $isMoreActive = collect($moreItems)->contains(fn (array $item): bool => request()->routeIs($item['active']));
@endphp

<div class="md:hidden" x-data="{ moreOpen: false }"
    x-effect="document.body.classList.toggle('overflow-hidden', moreOpen)"
    @keydown.escape.window="if (moreOpen) { moreOpen = false; $refs.moreButton.focus() }">

    {{-- Fundo escurecido: toque fora fecha a folha --}}
    <div x-show="moreOpen" x-cloak @click="moreOpen = false"
        x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[55] bg-black/40" aria-hidden="true"></div>

    {{-- Folha "Mais": fica logo acima da barra, que continua visível --}}
    <div id="mobile-more-menu" x-show="moreOpen" x-cloak x-ref="sheet"
        role="dialog" aria-modal="true" aria-labelledby="mobile-more-menu-title"
        x-init="$watch('moreOpen', (isOpen) => isOpen && $nextTick(() => $refs.sheet.querySelector('a')?.focus()))"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-4 opacity-0"
        class="fixed inset-x-0 bottom-[calc(60px+env(safe-area-inset-bottom,0px))] z-[56] rounded-t-xl border-t border-glass
               bg-surface-container-lowest px-gutter pb-gutter pt-sm shadow-[0_-12px_32px_rgba(0,97,164,0.12)]">

        <div class="mx-auto mb-sm h-1 w-10 rounded-full bg-outline-variant" aria-hidden="true"></div>

        <h2 id="mobile-more-menu-title" class="mb-sm px-1 text-xs font-bold uppercase tracking-wider text-on-surface-variant">
            {{ __('Mais opções') }}
        </h2>

        <nav aria-label="{{ __('Mais opções') }}" class="mx-auto grid max-w-lg grid-cols-3 gap-2">
            @foreach ($moreItems as $item)
                @php($isActive = request()->routeIs($item['active']))

                <a href="{{ route($item['route']) }}" @if ($isActive) aria-current="page" @endif @class([
                    'group flex min-h-[76px] min-w-0 flex-col items-center justify-center gap-1.5 rounded-xl px-1 py-sm text-center select-none',
                    'outline-none transition-all duration-normal ease-bounce focus-visible:ring-2 focus-visible:ring-primary active:scale-90',
                    'bg-primary-container text-on-primary-container' => $isActive,
                    'bg-surface-container-low text-on-surface hover:bg-surface-container hover:text-primary' => ! $isActive,
                ])>
                    <span class="flex transition-all duration-fast group-hover:-translate-y-0.5 group-hover:scale-110">
                        <x-ui.icon :name="$item['icon']" :filled="$isActive" size="text-[1.45rem]" :class="$isActive ? '' : 'text-primary'" />
                    </span>
                    <span class="max-w-full truncate text-xs font-semibold leading-4">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <nav role="navigation" aria-label="{{ __('Menu Mobile') }}"
        class="fixed inset-x-0 bottom-0 z-[60] flex items-center justify-around border-t border-glass bg-header/95 px-2
               h-[calc(60px+env(safe-area-inset-bottom,0px))] pb-[env(safe-area-inset-bottom,0px)] shadow-[0_-8px_30px_rgba(0,97,164,0.08)] backdrop-blur-xl">

        <div class="mx-auto flex w-full max-w-lg items-center justify-between">
            @foreach ($primaryItems as $item)
                <x-ui.bottom-nav-link :href="route($item['route'])" :icon="$item['icon']" :active="request()->routeIs($item['active'])">
                    {{ $item['label'] }}
                </x-ui.bottom-nav-link>
            @endforeach

            <x-ui.bottom-nav-link icon="more_horiz" :active="$isMoreActive" x-ref="moreButton"
                x-on:click="moreOpen = ! moreOpen" x-bind:aria-expanded="moreOpen.toString()" aria-controls="mobile-more-menu" aria-haspopup="dialog">
                {{ __('Mais') }}
            </x-ui.bottom-nav-link>
        </div>
    </nav>
</div>
