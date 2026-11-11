{{--
    Barra inferior flutuante do mobile com botão central "+" em destaque.
    Esquerda: Início e Matérias.
    Centro: Botão "+" que abre a folha com as demais opções (Boletim, Amigos, Mensagens).
    Direita: Agenda e Praticar.
--}}
@php
    $leftItems = [
        ['route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'home', 'label' => __('Início')],
        ['route' => 'enrollments.index', 'active' => 'enrollments.*', 'icon' => 'menu_book', 'label' => __('Matérias')],
    ];

    $rightItems = [
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

<div class="md:hidden"
    x-data="{
        moreOpen: false,
        navHidden: false,
        lastScrollY: 0,
        handleScroll() {
            const currentScrollY = Math.max(0, window.scrollY || window.pageYOffset || 0);

            if (currentScrollY <= 24 || this.moreOpen) {
                this.navHidden = false;
                this.lastScrollY = currentScrollY;
                return;
            }

            const delta = currentScrollY - this.lastScrollY;

            if (Math.abs(delta) < 8) {
                return;
            }

            this.navHidden = delta > 0;
            this.lastScrollY = currentScrollY;
        }
    }"
    x-init="lastScrollY = Math.max(0, window.scrollY || window.pageYOffset || 0)"
    @scroll.window.passive="handleScroll()"
    x-effect="document.body.classList.toggle('overflow-hidden', moreOpen)"
    @keydown.escape.window="if (moreOpen) { moreOpen = false; $refs.moreButton.focus() }">

    {{-- Fundo escurecido: toque fora fecha a folha --}}
    <div x-show="moreOpen" x-cloak @click="moreOpen = false"
        x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[55] bg-black/40 backdrop-blur-[2px]" aria-hidden="true"></div>

    {{-- Folha "Mais opções": abre logo acima da barra flutuante --}}
    <div id="mobile-more-menu" x-show="moreOpen" x-cloak x-ref="sheet"
        role="dialog" aria-modal="true" aria-labelledby="mobile-more-menu-title"
        x-init="$watch('moreOpen', (isOpen) => isOpen && $nextTick(() => $refs.sheet.querySelector('a')?.focus()))"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-4 opacity-0 scale-95" x-transition:enter-end="translate-y-0 opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0 opacity-100 scale-100" x-transition:leave-end="translate-y-4 opacity-0 scale-95"
        class="fixed inset-x-3 bottom-[calc(86px+env(safe-area-inset-bottom,0px))] z-[56] mx-auto max-w-md rounded-xl border border-glass
               bg-white dark:bg-[#191b24] px-gutter pb-gutter pt-sm shadow-[0_-12px_36px_rgba(0,97,164,0.16)] dark:shadow-[0_-12px_36px_rgba(0,0,0,0.6)]">

        <div class="mx-auto mb-sm h-1 w-10 rounded-full bg-outline-variant" aria-hidden="true"></div>

        <h2 id="mobile-more-menu-title" class="mb-sm px-1 text-xs font-bold uppercase tracking-wider text-on-surface-variant">
            {{ __('Mais opções') }}
        </h2>

        <nav aria-label="{{ __('Mais opções') }}" class="mx-auto grid max-w-lg grid-cols-3 gap-2">
            @foreach ($moreItems as $item)
                @php($isActive = request()->routeIs($item['active']))

                <a href="{{ route($item['route']) }}" @if ($isActive) aria-current="page" @endif @class([
                    'group flex min-h-[76px] min-w-0 flex-col items-center justify-center gap-1.5 rounded-lg px-1 py-sm text-center select-none',
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

    {{-- Barra de Navegação Flutuante (Estilo Pílula com Curva Central e Botão "+") --}}
    <nav role="navigation" aria-label="{{ __('Menu Mobile') }}"
        :class="(navHidden && !moreOpen) ? 'translate-y-28 opacity-0 pointer-events-none' : 'translate-y-0 opacity-100'"
        class="fixed inset-x-3 bottom-[calc(10px+env(safe-area-inset-bottom,0px))] z-[60] mx-auto max-w-md
               transition-all duration-300 ease-out will-change-transform">

        <div class="relative flex h-16 w-full items-center justify-between rounded-full bg-white px-4
                    shadow-[0_10px_30px_rgba(0,97,164,0.12)] transition-colors duration-normal
                    dark:bg-[#191b24] dark:shadow-[0_14px_34px_rgba(0,0,0,0.65)]">

            {{-- Curva orgânica superior central (Bump da referência visual) --}}
            <svg viewBox="0 0 120 14" aria-hidden="true"
                class="pointer-events-none absolute -top-[11px] left-1/2 h-[12px] w-28 -translate-x-1/2 fill-white transition-colors duration-normal dark:fill-[#191b24]">
                <path d="M0,14 C28,14 38,0 60,0 C82,0 92,14 120,14 Z" />
            </svg>

            {{-- 2 Ícones da Esquerda: Início e Matérias --}}
            <div class="flex flex-1 items-center justify-around">
                @foreach ($leftItems as $item)
                    <x-ui.bottom-nav-link :href="route($item['route'])" :icon="$item['icon']" :active="request()->routeIs($item['active'])">
                        {{ $item['label'] }}
                    </x-ui.bottom-nav-link>
                @endforeach
            </div>

            {{-- Botão Central "+" (Substitui os 3 pontos para abrir as demais opções) --}}
            <div class="relative mx-2 flex shrink-0 items-center justify-center">
                {{-- Anel pontilhado sutil no modo claro (igual à imagem de referência) --}}
                <span aria-hidden="true"
                    class="pointer-events-none absolute -inset-1.5 rounded-full border border-dashed border-primary/35 dark:hidden"></span>

                <button type="button"
                    x-ref="moreButton"
                    x-on:click="moreOpen = ! moreOpen"
                    x-bind:aria-expanded="moreOpen.toString()"
                    aria-controls="mobile-more-menu"
                    aria-haspopup="dialog"
                    title="{{ __('Mais') }}"
                    aria-label="{{ __('Mais opções') }}"
                    @class([
                        'group relative flex size-12 items-center justify-center rounded-full select-none outline-none',
                        'transition-all duration-bounce ease-bounce hover:scale-105 active:scale-90 focus-visible:ring-2 focus-visible:ring-primary',
                        'bg-gradient-to-tr from-primary to-primary-container text-white shadow-[0_6px_20px_rgba(33,150,243,0.45)]',
                        'dark:bg-[#191b24] dark:from-transparent dark:to-transparent dark:border-2 dark:border-primary dark:text-primary',
                        'dark:shadow-[0_0_18px_rgba(158,202,255,0.45),inset_0_0_10px_rgba(158,202,255,0.2)]',
                        'ring-2 ring-primary/50' => $isMoreActive,
                    ])>
                    <span class="flex items-center justify-center transition-transform duration-normal ease-bounce"
                        :class="moreOpen && 'rotate-45'">
                        <x-ui.icon name="add" size="text-2xl" />
                    </span>
                    <span class="sr-only">{{ __('Mais') }}</span>
                </button>
            </div>

            {{-- 2 Ícones da Direita: Agenda e Praticar --}}
            <div class="flex flex-1 items-center justify-around">
                @foreach ($rightItems as $item)
                    <x-ui.bottom-nav-link :href="route($item['route'])" :icon="$item['icon']" :active="request()->routeIs($item['active'])">
                        {{ $item['label'] }}
                    </x-ui.bottom-nav-link>
                @endforeach
            </div>

        </div>
    </nav>
</div>
