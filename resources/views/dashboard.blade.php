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
         2. VERSÃO MOBILE (< md): REDESIGN BASEADO NA IMAGEM DE REFERÊNCIA
         ========================================================================= --}}
    <div class="flex md:hidden flex-col gap-6 pb-20">

        {{-- SEÇÃO 1: DISCIPLINAS (SUBJECTS) --}}
        <section class="flex flex-col gap-3" aria-label="{{ __('Suas Disciplinas') }}">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-primary dark:text-white">
                        {{ __('Disciplinas') }}
                    </h2>
                    <p class="text-xs text-on-surface-variant">
                        {{ __('Matérias em andamento neste período') }}
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
                <div class="grid grid-cols-2 gap-3">
                    @foreach ($enrollments->take(4) as $enrollment)
                        @php
                            $subject = $enrollment->subject();
                            $color = $subject->color ?? '#0061a4';
                        @endphp
                        <a href="{{ route('enrollments.show', $enrollment) }}"
                           style="background: linear-gradient(135deg, {{ $color }}ee, {{ $color }}cc);"
                           class="group relative flex flex-col justify-between overflow-hidden rounded-2xl p-4 text-white shadow-md transition-all duration-normal ease-bounce hover:-translate-y-1 hover:shadow-xl hover:scale-[1.02] active:scale-95">
                            <div class="flex items-center justify-between">
                                <span class="flex size-9 items-center justify-center rounded-xl bg-white/20 text-sm font-bold backdrop-blur-sm transition-transform duration-normal group-hover:scale-110 group-hover:rotate-6">
                                    <x-ui.icon name="menu_book" size="text-lg" />
                                </span>
                                <x-ui.icon name="more_vert" size="text-lg" class="text-white/60 transition-colors group-hover:text-white" />
                            </div>

                            <div class="mt-6">
                                <span class="text-[0.65rem] font-semibold uppercase tracking-wider text-white/80">
                                    {{ $subject->code }}
                                </span>
                                <h3 class="text-sm font-bold leading-tight line-clamp-1">
                                    {{ $subject->name }}
                                </h3>
                                
                                @if ($enrollment->attendance_rate !== null)
                                    <span class="mt-1 inline-block text-[0.65rem] text-white/90">
                                        {{ (int) round($enrollment->attendance_rate) }}% {{ __('frequência') }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- SEÇÃO 2: SEUS HORÁRIOS & PRÓXIMAS AULAS (YOUR SCHEDULE) --}}
        <section class="flex flex-col gap-3" aria-label="{{ __('Seus Horários') }}">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-primary dark:text-white">
                        {{ __('Seus Horários') }}
                    </h2>
                    <p class="text-xs text-on-surface-variant">
                        {{ __('Próximas aulas e compromissos') }}
                    </p>
                </div>

                <a href="{{ route('agenda') }}" 
                   class="inline-flex items-center gap-1 text-xs font-bold text-primary transition-all duration-fast hover:translate-x-1 hover:underline">
                    {{ __('Agenda completa') }} →
                </a>
            </div>

            @if ($upcoming->isEmpty())
                <div class="flex items-center gap-3 rounded-2xl border border-glass bg-surface-container-lowest p-4 text-xs text-on-surface-variant">
                    <x-ui.icon name="event_available" size="text-xl" class="text-primary" />
                    {{ __('Nenhum compromisso agendado para os próximos dias.') }}
                </div>
            @else
                <div class="flex flex-col gap-2.5">
                    @foreach ($upcoming->take(3) as $item)
                        <a href="{{ $item->url ?? route('agenda') }}"
                           class="group relative flex items-center justify-between overflow-hidden rounded-2xl border border-glass bg-surface-container-lowest p-4 shadow-sm transition-all duration-normal ease-bounce hover:-translate-y-1 hover:border-primary/40 hover:shadow-md active:scale-95">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-fixed text-primary font-bold transition-transform duration-normal group-hover:scale-110"
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
                                        <span class="inline-flex items-center rounded-md bg-surface-container-high px-1.5 py-0.5 text-[0.65rem] font-medium text-on-surface-variant">
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
        <section id="desafios-mobile" class="scroll-mt-28" aria-label="{{ __('Desafios Diários') }}">
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

</x-app-layout>
