<x-app-layout>

    {{-- =========================================================================
         1. VERSÃO DESKTOP (>= md): LAYOUT ORIGINAL 100% PRESERVADO
         ========================================================================= --}}
    <div class="hidden md:flex flex-col gap-md">
        {{-- Linha 1: estatísticas rápidas --}}
        <section class="grid grid-cols-1 gap-md md:grid-cols-2" aria-label="{{ __('Estatísticas Rápidas') }}">
            <x-dashboard.streaks :board="$dailyBoard" :friends="$friends" />
            <x-dashboard.grades :enrollments="$enrollments" />
        </section>

        {{-- Linha 2: revisão diária e cronograma --}}
        <section class="grid grid-cols-1 gap-md lg:grid-cols-[2fr_1fr]" aria-label="{{ __('Estudos e Atividades') }}">
            <x-dashboard.flashcard :deck="$deck" />
            <x-dashboard.upcoming :items="$upcoming" />
        </section>

        {{-- Linha 3: desafios --}}
        <section id="desafios" class="scroll-mt-28" aria-label="{{ __('Desafios Diários') }}">
            <x-dashboard.challenges :board="$dailyBoard" />
        </section>
    </div>


    {{-- =========================================================================
         2. VERSÃO MOBILE (< md): HOME PAGE COM BANNER E SHEET ARREDONDADO
         ========================================================================= --}}
    <div class="flex md:hidden min-h-screen flex-col justify-between">

        {{-- BANNER SUPERIOR DECORATIVO --}}
        <div class="relative w-full overflow-hidden bg-gradient-to-b from-primary to-secondary pt-safe pb-16 text-on-primary">
            {{-- Formas Orgânicas Decorativas --}}
            <div class="pointer-events-none absolute -right-12 -top-12 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -left-16 bottom-0 h-48 w-48 rounded-full bg-primary-container/20 blur-xl"></div>

            @php
                $currentUser = auth()->user();
                $userAvatar = $currentUser?->avatar
                    ?? $currentUser?->oauthIdentities()->whereNotNull('avatar')->value('avatar')
                    ?? 'https://ui-avatars.com/api/?name='.urlencode($currentUser?->name ?? 'U').'&background=ffffff&color=0061a4&size=256&bold=true';
            @endphp

            {{-- Topbar da Home Mobile --}}
            <header class="relative mx-auto flex w-full max-w-4xl items-center justify-between px-gutter pt-4">
                <a href="{{ route('profile.edit') }}"
                   title="{{ __('Configurações') }}"
                   aria-label="{{ __('Configurações') }}"
                   class="flex size-11 items-center justify-center rounded-full bg-white/15 text-white shadow-sm backdrop-blur-md transition-all duration-normal ease-bounce hover:scale-110 hover:rotate-45 hover:bg-white/25 hover:shadow-lg active:scale-90">
                    <x-ui.icon name="settings" size="text-2xl" />
                </a>

                <div class="flex items-center gap-2">
                    <x-application-logo class="w-8 drop-shadow-sm" />
                    <span class="text-xl font-extrabold tracking-tight text-white">UniFocus</span>
                </div>

                <button type="button" x-data @click="$store.theme.toggle()"
                    :aria-label="$store.theme.isDark ? '{{ __('Ativar tema claro') }}' : '{{ __('Ativar tema escuro') }}'"
                    :title="$store.theme.isDark ? '{{ __('Tema claro') }}' : '{{ __('Tema escuro') }}'"
                    class="flex size-11 items-center justify-center rounded-full bg-white/15 text-white shadow-sm backdrop-blur-md transition-all duration-normal ease-bounce hover:scale-110 hover:rotate-12 hover:bg-white/25 hover:shadow-lg active:scale-90">
                    <x-ui.icon name="dark_mode" x-show="! $store.theme.isDark" />
                    <x-ui.icon name="light_mode" x-show="$store.theme.isDark" x-cloak />
                </button>
            </header>

            {{-- Imagem de Perfil do Usuário em Destaque Central --}}
            <div class="relative mx-auto mt-4 flex max-w-sm flex-col items-center justify-center px-gutter text-center">
                <a href="{{ route('profile.edit') }}"
                   title="{{ __('Ir para o perfil') }}"
                   aria-label="{{ __('Ir para o perfil de :name', ['name' => $currentUser?->name]) }}"
                   class="relative flex items-center justify-center group cursor-pointer">
                    <div class="relative flex size-28 items-center justify-center overflow-hidden rounded-xl border-2 border-white/30 bg-white/15 p-1.5 shadow-inner backdrop-blur-md transition-all duration-normal ease-bounce group-hover:scale-105 group-hover:border-white/60 group-hover:bg-white/25">
                        <img src="{{ $userAvatar }}"
                             alt="{{ $currentUser?->name }}"
                             class="size-full rounded-lg object-cover shadow-md transition-transform duration-normal group-hover:scale-105" />
                    </div>
                    <div class="absolute -bottom-2 -right-2 flex size-10 items-center justify-center rounded-full bg-streak shadow-lg transition-transform duration-normal ease-bounce group-hover:scale-110 group-hover:rotate-12">
                        <x-ui.icon name="local_fire_department" filled size="text-2xl" class="text-white animate-flame-pulse" />
                    </div>
                </a>

                <span class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold tracking-wide text-white backdrop-blur-sm transition-transform duration-normal hover:scale-105">
                    <span class="size-2 rounded-full bg-streak animate-pulse"></span>
                    {{ __('Sua vida universitária em foco') }}
                </span>
            </div>
        </div>

        {{-- SHEET CARD PRINCIPAL (BORDAS SUPERIORES ARREDONDADAS rounded-t-[32px]) --}}
        <div class="relative -mt-8 flex-1 rounded-t-[32px] border-t border-card-border bg-card px-gutter pt-6 pb-28 shadow-card transition-colors duration-normal">
            <div class="mx-auto flex w-full max-w-xl flex-col gap-6">

                {{-- SEÇÃO 1: DISCIPLINAS (SUBJECTS) --}}
                <section class="flex flex-col gap-3" aria-label="{{ __('Suas Disciplinas') }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-extrabold tracking-tight text-primary dark:text-white">
                                {{ __('Disciplinas') }}
                            </h2>
                            <p class="text-xs text-on-surface-variant">
                                {{ __('Exploração e recomendações do período') }}
                            </p>
                        </div>

                        <a href="{{ route('enrollments.index') }}"
                           class="inline-flex items-center gap-1 text-xs font-bold text-primary transition-all duration-fast hover:translate-x-1 hover:underline">
                            {{ __('Ver todas') }} →
                        </a>
                    </div>

                    @if ($enrollments->isEmpty())
                        <x-ui.empty-state icon="menu_book" :title="__('Nenhuma matéria em andamento')"
                            :description="__('Adicione suas matérias para acompanhar notas e faltas.')">
                            <x-ui.button :href="route('enrollments.create')">{{ __('Adicionar matéria') }}</x-ui.button>
                        </x-ui.empty-state>
                    @else
                        @php
                            $cardGradients = [
                                'bg-gradient-to-br from-primary to-primary-hover text-on-primary',
                                'bg-gradient-to-br from-secondary to-secondary-container text-on-secondary',
                                'bg-gradient-to-br from-tertiary to-tertiary-container text-on-tertiary',
                                'bg-gradient-to-br from-primary-container to-primary text-on-primary',
                            ];
                            $cardIcons = ['code', 'database', 'terminal', 'memory'];
                        @endphp
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($enrollments->take(4) as $enrollment)
                                @php
                                    $subject = $enrollment->subject();
                                    $gradientClass = $cardGradients[$loop->index % count($cardGradients)];
                                    $iconName = $cardIcons[$loop->index % count($cardIcons)];
                                @endphp
                                <a href="{{ route('enrollments.show', $enrollment) }}"
                                   class="group relative flex flex-col justify-between overflow-hidden rounded-xl {{ $gradientClass }} p-4 shadow-md transition-all duration-normal ease-bounce hover:-translate-y-1 hover:shadow-xl hover:scale-[1.02] active:scale-95">
                                    <div class="flex items-center justify-between">
                                        <span class="flex size-9 items-center justify-center rounded-full bg-white/20 text-sm font-bold backdrop-blur-sm transition-transform duration-normal group-hover:scale-110 group-hover:rotate-6">
                                            <x-ui.icon :name="$iconName" size="text-lg" />
                                        </span>
                                        <x-ui.icon name="more_vert" size="text-lg" class=" opacity-60 transition-opacity group-hover:opacity-100" />
                                    </div>

                                    <div class="mt-6">
                                        <span class="text-[0.65rem] font-semibold uppercase tracking-wider opacity-80">
                                            {{ $subject->code }}
                                        </span>
                                        <h3 class="text-sm font-bold leading-tight line-clamp-1">
                                            {{ $subject->name }}
                                        </h3>

                                        @if ($enrollment->attendance_rate !== null)
                                            <span class="mt-1 inline-block text-[0.65rem] opacity-90">
                                                {{ (int) round($enrollment->attendance_rate) }}% {{ __('frequência') }}
                                            </span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- SEÇÃO 2: SEUS HORÁRIOS & PRÓXIMAS AULAS --}}
                <section class="flex flex-col gap-3" aria-label="{{ __('Seus Horários') }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-extrabold tracking-tight text-primary dark:text-white">
                                {{ __('Seus Horários') }}
                            </h2>
                            <p class="text-xs text-on-surface-variant">
                                {{ __('Próximas aulas e atividades programadas') }}
                            </p>
                        </div>

                        <a href="{{ route('agenda') }}"
                           class="inline-flex items-center gap-1 text-xs font-bold text-primary transition-all duration-fast hover:translate-x-1 hover:underline">
                            {{ __('Agenda completa') }} →
                        </a>
                    </div>

                    @if ($upcoming->isEmpty())
                        <div class="flex items-center gap-3 rounded-xl border border-glass bg-surface-container-lowest p-4 text-xs text-on-surface-variant">
                            <x-ui.icon name="event_available" size="text-xl" class="text-primary" />
                            {{ __('Nenhum compromisso agendado para os próximos dias.') }}
                        </div>
                    @else
                        <div class="flex flex-col gap-2.5">
                            @foreach ($upcoming->take(3) as $item)
                                <a href="{{ $item->url ?? route('agenda') }}"
                                   class="group relative flex items-center justify-between overflow-hidden rounded-xl border border-glass bg-surface-container-lowest p-4 shadow-sm transition-all duration-normal ease-bounce hover:-translate-y-1 hover:border-primary/40 hover:shadow-md active:scale-95">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-primary-fixed text-primary font-bold transition-transform duration-normal group-hover:scale-110"
                                             @if ($item->color) style="color: {{ $item->color }}; background-color: {{ $item->color }}1a;" @endif>
                                            <x-ui.icon :name="$item->icon ?? 'event'" size="text-xl" />
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-extrabold text-primary">
                                                    @if ($item->startsAt)
                                                        {{ $item->startsAt->format('H:i') }}
                                                    @endif
                                                </span>
                                                <span class="inline-flex items-center rounded-sm bg-surface-container-high px-1.5 py-0.5 text-[0.65rem] font-medium text-on-surface-variant">
                                                    {{ $item->startsAt?->translatedFormat('D, d/m') }}
                                                </span>
                                            </div>
                                            <h4 class="text-sm font-bold text-on-surface truncate">{{ $item->title }}</h4>
                                            <p class="text-xs text-on-surface-variant truncate">{{ $item->context }}</p>
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center ml-2">
                                        <x-ui.icon name="chevron_right" size="text-xl" class="text-on-surface-variant/60 transition-all duration-fast group-hover:translate-x-1 group-hover:text-primary" />
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- SEÇÃO 3: FOGUINHO & DESAFIOS DIÁRIOS --}}
                <section id="desafios-mobile" class="scroll-mt-8" aria-label="{{ __('Desafios Diários') }}">
                    <x-dashboard.streaks :board="$dailyBoard" :friends="$friends" />
                    <div class="mt-4">
                        <x-dashboard.challenges :board="$dailyBoard" />
                    </div>
                </section>

                {{-- SEÇÃO 4: PRÁTICA RÁPIDA / FLASHCARD --}}
                <section aria-label="{{ __('Revisão Rápida') }}">
                    <x-dashboard.flashcard :deck="$deck" />
                </section>

            </div>
        </div>

    </div>

</x-app-layout>
