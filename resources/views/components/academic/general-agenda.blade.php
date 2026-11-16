@props(['calendar', 'daysAhead' => 30])

@php
    $subjects = $calendar['subjects'];
    $activities = collect($calendar['activities']);
    $lessons = collect($calendar['lessons']);
    $events = collect($calendar['events']);

    $overdueActivities = $activities->where('isOverdue', true)->values();
    $upcomingActivities = $activities->where('isOverdue', false)->values();
    $upcomingLessons = $lessons->where('isPast', false)->values();
    $lessonDays = $upcomingLessons->groupBy('date');

    $totalReminders = $activities->count() + $upcomingLessons->count() + $events->count();
    $today = today();
@endphp

<div x-data="generalAgenda({
        initialYear: {{ (int) $today->format('Y') }},
        initialMonth: {{ (int) $today->format('n') - 1 }},
        today: @js($today->format('Y-m-d')),
        activities: @js($activities->values()->all()),
        lessons: @js($lessons->values()->all()),
        events: @js($events->values()->all()),
    })" class="flex flex-col gap-md">

    {{-- Cabeçalho da Agenda com alternador Calendário / Lembretes --}}
    <x-ui.page-header :title="__('Agenda')"
        :description="__('Acompanhe provas, entregas, aulas e avisos de todas as suas matérias em um só lugar.')">
        <x-slot:actions>
            <div class="inline-flex rounded-full border border-surface-variant bg-surface-container-low p-1" role="tablist"
                aria-label="{{ __('Visualização da agenda') }}">
                <button type="button" role="tab" :aria-selected="(view === 'calendar').toString()" @click="view = 'calendar'"
                    :class="view === 'calendar'
                        ? 'bg-primary text-on-primary shadow-sm'
                        : 'text-on-surface-variant hover:text-on-surface'"
                    class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-bold transition duration-fast">
                    <x-ui.icon name="calendar_month" size="text-base" />
                    <span>{{ __('Calendário') }}</span>
                </button>

                <button type="button" role="tab" :aria-selected="(view === 'list').toString()" @click="view = 'list'"
                    :class="view === 'list'
                        ? 'bg-primary text-on-primary shadow-sm'
                        : 'text-on-surface-variant hover:text-on-surface'"
                    class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-bold transition duration-fast">
                    <x-ui.icon name="view_agenda" size="text-base" />
                    <span>{{ __('Lembretes') }}</span>
                    @if ($totalReminders > 0)
                        <span :class="view === 'list' ? 'bg-white/20 dark:bg-slate-900/20' : 'bg-surface-container-high'"
                            class="rounded-full px-1.5 py-0.5 font-mono text-[0.65rem] leading-none">
                            {{ $totalReminders }}
                        </span>
                    @endif
                </button>
            </div>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Barra de filtros (Matéria e Categoria) --}}
    <x-ui.card :inner="false" class="p-sm">
        <div class="flex flex-col gap-sm lg:flex-row lg:items-center lg:justify-between">
            {{-- Filtro por matéria --}}
            <div class="flex flex-wrap items-center gap-1.5" role="group" aria-label="{{ __('Filtrar por matéria') }}">
                <button type="button" @click="selectedSubject = 'all'"
                    :class="selectedSubject === 'all'
                        ? 'bg-primary text-on-primary shadow-sm'
                        : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold transition duration-fast">
                    {{ __('Todas as matérias') }}
                </button>

                @foreach ($subjects as $subject)
                    <button type="button" @click="selectedSubject = {{ $subject['id'] }}"
                        :class="Number(selectedSubject) === {{ $subject['id'] }}
                            ? 'bg-primary text-on-primary shadow-sm'
                            : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition duration-fast">
                        <span class="size-2 shrink-0 rounded-full" style="background-color: {{ $subject['color'] }}"></span>
                        <span class="max-w-[160px] truncate">{{ $subject['name'] }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Filtro por tipo --}}
            <div class="flex flex-wrap items-center gap-1.5 border-t border-surface-variant pt-sm lg:border-t-0 lg:pt-0"
                role="group" aria-label="{{ __('Filtrar por tipo') }}">
                @foreach ([
                    'all' => __('Tudo'),
                    'deadlines' => __('Provas e entregas'),
                    'lessons' => __('Aulas'),
                    'events' => __('Avisos'),
                ] as $catKey => $catLabel)
                    <button type="button" @click="selectedCategory = '{{ $catKey }}'"
                        :class="selectedCategory === '{{ $catKey }}'
                            ? 'bg-secondary-fixed text-on-secondary-fixed font-bold'
                            : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface'"
                        class="rounded-full px-2.5 py-1 text-xs font-semibold transition duration-fast">
                        {{ $catLabel }}
                    </button>
                @endforeach
            </div>
        </div>
    </x-ui.card>

    {{-- Alerta rápido de entregas atrasadas na visão Calendário --}}
    @if ($overdueActivities->isNotEmpty())
        <div x-show="view === 'calendar'"
            class="flex flex-wrap items-center justify-between gap-sm rounded-lg border border-error bg-error-container px-md py-sm text-on-error-container">
            <div class="flex items-center gap-2 text-sm font-semibold">
                <x-ui.icon name="warning" size="text-xl" />
                <span>
                    {{ trans_choice('{1} Você tem :count entrega atrasada pendente.|[2,*] Você tem :count entregas atrasadas pendentes.', $overdueActivities->count()) }}
                </span>
            </div>

            <button type="button" @click="view = 'list'; selectedCategory = 'deadlines'"
                class="text-xs font-extrabold underline hover:opacity-80">
                {{ __('Ver atrasadas') }} →
            </button>
        </div>
    @endif

    {{-- VISÃO 1: CALENDÁRIO MENSAL GERAL + PAINEL DO DIA --}}
    <div x-show="view === 'calendar'" class="grid grid-cols-1 items-start gap-md lg:grid-cols-[minmax(0,430px)_1fr]">

        {{-- Grade mensal --}}
        <x-ui.card>
            <div class="rounded-xl border border-surface-variant bg-surface-container-low p-3 sm:p-4">
                {{-- Barra superior: < [Mês] [Ano] > --}}
                <div class="mb-3 flex items-center justify-between gap-2">
                    <button type="button" @click="prevMonth()" aria-label="{{ __('Mês anterior') }}"
                        class="flex size-9 shrink-0 items-center justify-center rounded-full border border-surface-variant bg-surface-container-lowest text-on-surface shadow-sm transition duration-fast hover:border-primary hover:text-primary active:scale-90">
                        <x-ui.icon name="chevron_left" size="text-xl" />
                    </button>

                    <div class="flex items-center gap-1.5">
                        <label for="agenda-calendar-month" class="sr-only">{{ __('Mês') }}</label>
                        <select id="agenda-calendar-month" x-model.number="month"
                            class="rounded-lg border border-surface-variant bg-surface-container-lowest py-1.5 pl-3 pr-7 text-sm font-bold text-on-surface shadow-sm focus:border-primary focus:ring-primary">
                            <template x-for="(label, idx) in months" :key="idx">
                                <option :value="idx" :selected="idx === month" x-text="label"></option>
                            </template>
                        </select>

                        <label for="agenda-calendar-year" class="sr-only">{{ __('Ano') }}</label>
                        <select id="agenda-calendar-year" x-model.number="year"
                            class="rounded-lg border border-surface-variant bg-surface-container-lowest py-1.5 pl-3 pr-7 font-mono text-sm font-bold text-on-surface shadow-sm focus:border-primary focus:ring-primary">
                            <template x-for="y in yearOptions" :key="y">
                                <option :value="y" :selected="y === year" x-text="y"></option>
                            </template>
                        </select>
                    </div>

                    <button type="button" @click="nextMonth()" aria-label="{{ __('Próximo mês') }}"
                        class="flex size-9 shrink-0 items-center justify-center rounded-full border border-surface-variant bg-surface-container-lowest text-on-surface shadow-sm transition duration-fast hover:border-primary hover:text-primary active:scale-90">
                        <x-ui.icon name="chevron_right" size="text-xl" />
                    </button>
                </div>

                {{-- Cabeçalho dos dias da semana (Dom - Sáb) --}}
                <div class="mb-1.5 grid grid-cols-7 gap-1.5 text-center">
                    <template x-for="dayName in weekdays" :key="dayName">
                        <span class="py-1 text-xs font-bold text-on-surface-variant" x-text="dayName"></span>
                    </template>
                </div>

                {{-- Células dos dias --}}
                <div class="grid grid-cols-7 gap-1.5">
                    <template x-for="cell in cells" :key="cell.iso">
                        <button type="button" @click="selectDay(cell)"
                            :aria-label="cell.iso + (cell.count ? ' (' + cell.count + ')' : '')"
                            :class="[
                                'relative flex h-10 sm:h-11 flex-col items-center justify-center rounded-lg text-sm font-semibold transition duration-fast active:scale-95',
                                !cell.inMonth && !cell.hasOverdue && !cell.hasExam && !cell.hasDelivery && !cell.hasEvent
                                    ? 'bg-transparent text-outline opacity-50'
                                    : '',
                                cell.hasOverdue
                                    ? 'bg-error text-on-error shadow-sm hover:opacity-95'
                                    : (cell.hasExam
                                        ? 'bg-streak text-white shadow-sm hover:opacity-95'
                                        : (cell.hasDelivery
                                            ? 'bg-primary text-on-primary shadow-sm hover:bg-primary-hover'
                                            : (cell.hasEvent
                                                ? 'bg-primary-fixed text-on-primary-fixed dark:bg-primary-container dark:text-on-primary-container hover:opacity-90'
                                                : (cell.inMonth
                                                    ? 'border border-surface-variant bg-surface-container-lowest text-on-surface hover:border-primary'
                                                    : 'border border-transparent hover:border-surface-variant')))),
                                !cell.inMonth && (cell.hasOverdue || cell.hasExam || cell.hasDelivery || cell.hasEvent) ? 'opacity-70' : '',
                                cell.isToday ? 'ring-2 ring-primary ring-offset-1 ring-offset-card' : '',
                                selectedDate === cell.iso ? 'ring-2 ring-on-surface dark:ring-white' : '',
                            ]">
                            <span x-text="cell.day"></span>

                            <span x-show="cell.hasLesson || cell.count > 1"
                                :class="(cell.hasOverdue || cell.hasExam || cell.hasDelivery) ? 'bg-current opacity-85' : 'bg-primary'"
                                class="mt-0.5 size-1.5 rounded-full"></span>
                        </button>
                    </template>
                </div>

                {{-- Legenda compacta --}}
                <div class="mt-3 flex flex-wrap items-center justify-center gap-x-3 gap-y-1.5 border-t border-surface-variant pt-3 text-[0.7rem] font-semibold text-on-surface-variant">
                    <span class="inline-flex items-center gap-1">
                        <span class="size-2.5 rounded-sm bg-error"></span>
                        {{ __('Atrasada') }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="size-2.5 rounded-sm bg-streak"></span>
                        {{ __('Prova') }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="size-2.5 rounded-sm bg-primary"></span>
                        {{ __('Entrega') }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="size-2.5 rounded-sm border border-outline-variant bg-primary-fixed dark:bg-primary-container"></span>
                        {{ __('Recesso / Evento') }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="size-2 rounded-full bg-primary"></span>
                        {{ __('Aula') }}
                    </span>
                </div>
            </div>
        </x-ui.card>

        {{-- Painel lateral / abaixo: compromissos do dia selecionado --}}
        <x-ui.card>
            <div class="mb-sm flex items-center justify-between gap-sm border-b border-surface-variant pb-sm">
                <div>
                    <x-ui.card-title icon="today">
                        <span x-text="selectedDate ? '{{ __('Compromissos de') }} ' + selectedDateFormatted : '{{ __('Selecione um dia') }}'"></span>
                    </x-ui.card-title>
                    <p class="mt-0.5 text-xs text-on-surface-variant"
                        x-text="selectedDate ? (selectedItems.length + ' ' + (selectedItems.length === 1 ? '{{ __('item encontrado') }}' : '{{ __('itens encontrados') }}')) : '{{ __('Toque em uma data do calendário para ver provas, entregas, aulas e avisos.') }}'"></p>
                </div>

                <button type="button" @click="view = 'list'" class="shrink-0 text-xs font-bold text-primary hover:underline">
                    {{ __('Ver lembretes') }} →
                </button>
            </div>

            <template x-if="selectedDate && selectedItems.length > 0">
                <div class="flex flex-col gap-2.5">
                    <template x-for="item in selectedItems" :key="item.kind + '-' + item.id">
                        <div>
                            <template x-if="item.kind === 'activity' || item.kind === 'lesson'">
                                <a :href="item.url"
                                    class="group flex items-center justify-between gap-sm rounded-lg border border-surface-variant bg-surface-container-low px-3 py-2.5 transition duration-fast hover:border-primary hover:bg-surface-container">
                                    <span class="flex min-w-0 items-center gap-3">
                                        <span :class="item.isOverdue
                                                ? 'bg-error-container text-on-error-container'
                                                : (item.category === 'exam'
                                                    ? 'border border-streak-border bg-streak-bg text-streak'
                                                    : (item.category === 'delivery'
                                                        ? 'bg-primary text-on-primary'
                                                        : 'bg-primary-fixed text-on-primary-fixed dark:bg-primary-container dark:text-on-primary-container'))"
                                            class="flex size-9 shrink-0 items-center justify-center rounded-lg">
                                            <span class="icon text-xl" x-text="item.icon"></span>
                                        </span>

                                        <span class="flex min-w-0 flex-col">
                                            <span class="flex items-center gap-1.5 text-[0.7rem] font-bold text-on-surface-variant">
                                                <span class="size-2 shrink-0 rounded-full" :style="'background-color:' + item.subjectColor"></span>
                                                <span class="truncate" x-text="item.subjectName"></span>
                                            </span>
                                            <span class="truncate text-sm font-bold text-on-surface group-hover:text-primary" x-text="item.title"></span>
                                            <span class="text-xs text-on-surface-variant" x-text="item.typeLabel + ' · ' + item.timeLabel"></span>
                                        </span>
                                    </span>

                                    <span class="flex shrink-0 items-center gap-1.5">
                                        <span x-show="item.isOverdue" class="rounded-full bg-error-container px-2.5 py-0.5 font-mono text-[0.65rem] font-bold text-on-error-container">
                                            {{ __('Atrasada') }}
                                        </span>
                                        <span x-show="item.isUrgent && !item.isOverdue" class="rounded-full border border-streak-border bg-streak-bg px-2.5 py-0.5 font-mono text-[0.65rem] font-bold text-streak">
                                            {{ __('Urgente') }}
                                        </span>
                                        <span class="icon text-lg text-on-surface-variant">chevron_right</span>
                                    </span>
                                </a>
                            </template>

                            <template x-if="item.kind === 'event'">
                                <div class="flex items-center justify-between gap-sm rounded-lg border border-surface-variant bg-surface-container-low px-3 py-2.5">
                                    <span class="flex min-w-0 items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary-fixed text-on-primary-fixed dark:bg-primary-container dark:text-on-primary-container">
                                            <span class="icon text-xl" x-text="item.icon"></span>
                                        </span>

                                        <span class="flex min-w-0 flex-col">
                                            <span class="truncate text-[0.7rem] font-bold text-on-surface-variant" x-text="item.institutionName + ' · ' + item.termName"></span>
                                            <span class="truncate text-sm font-bold text-on-surface" x-text="item.title"></span>
                                            <span class="font-mono text-xs text-on-surface-variant" x-text="item.typeLabel + ' · ' + item.rangeLabel"></span>
                                        </span>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="!selectedDate || selectedItems.length === 0">
                <x-ui.empty-state icon="event_available" :title="__('Nenhum compromisso neste dia')"
                    :description="__('Clique em uma data destacada no calendário ou mude para a aba Lembretes para ver toda a programação.')" />
            </template>
        </x-ui.card>
    </div>

    {{-- VISÃO 2: LEMBRETES REORGANIZADOS POR CATEGORIA --}}
    <div x-show="view === 'list'" x-cloak class="flex flex-col gap-md">
        @if ($activities->isEmpty() && $upcomingLessons->isEmpty() && $events->isEmpty())
            <x-ui.card>
                <x-ui.empty-state icon="event_available" :title="__('Agenda livre')"
                    :description="__('Nada registrado ainda. Cadastre provas, atividades com prazo ou aulas nas suas matérias.')">
                    <x-ui.button :href="route('enrollments.index')" variant="secondary">{{ __('Ir para matérias') }}</x-ui.button>
                </x-ui.empty-state>
            </x-ui.card>
        @else
            {{-- 1. Atrasadas --}}
            @if ($overdueActivities->isNotEmpty())
                <x-ui.card x-show="matchesSection('deadlines')">
                    <div class="mb-sm flex items-center justify-between">
                        <x-ui.card-title icon="warning" class="!text-error">{{ __('Atrasadas') }}</x-ui.card-title>
                        <x-ui.badge variant="danger">{{ $overdueActivities->count() }}</x-ui.badge>
                    </div>

                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                        @foreach ($overdueActivities as $item)
                            <a href="{{ $item['url'] }}" x-show="matchesSubject({{ $item['subjectId'] }})"
                                class="group flex items-center justify-between gap-sm rounded-lg border border-error bg-error-container px-3 py-2.5 text-on-error-container transition duration-fast hover:opacity-95">
                                <span class="flex min-w-0 items-center gap-3">
                                    <span class="flex w-12 shrink-0 flex-col items-center justify-center rounded-md bg-card px-1 py-1 font-mono leading-tight text-error">
                                        <span class="text-sm font-extrabold">{{ $item['dayNum'] }}</span>
                                        <span class="text-[0.6rem] font-bold uppercase">{{ $item['monthShort'] }}</span>
                                    </span>

                                    <span class="flex min-w-0 flex-col">
                                        <span class="flex items-center gap-1.5 text-[0.7rem] font-bold opacity-85">
                                            <span class="size-2 shrink-0 rounded-full" style="background-color: {{ $item['subjectColor'] }}"></span>
                                            <span class="truncate">{{ $item['subjectName'] }}</span>
                                        </span>
                                        <span class="truncate text-sm font-bold">{{ $item['title'] }}</span>
                                        <span class="text-xs opacity-90">{{ $item['typeLabel'] }} · {{ $item['timeLabel'] }}</span>
                                    </span>
                                </span>

                                <x-ui.icon name="chevron_right" size="text-lg" class="shrink-0" />
                            </a>
                        @endforeach
                    </div>
                </x-ui.card>
            @endif

            {{-- 2. Próximas Provas e Entregas --}}
            <x-ui.card x-show="matchesSection('deadlines')">
                <div class="mb-sm flex items-center justify-between border-b border-surface-variant pb-sm">
                    <x-ui.card-title icon="assignment">{{ __('Próximas provas e entregas') }}</x-ui.card-title>
                    <span class="font-mono text-xs text-on-surface-variant">{{ $upcomingActivities->count() }}</span>
                </div>

                @if ($upcomingActivities->isEmpty())
                    <p class="text-sm text-on-surface-variant">{{ __('Nenhuma prova ou entrega pendente para os próximos dias.') }}</p>
                @else
                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                        @foreach ($upcomingActivities as $item)
                            <a href="{{ $item['url'] }}" x-show="matchesSubject({{ $item['subjectId'] }})"
                                class="group flex items-center justify-between gap-sm rounded-lg border border-surface-variant bg-surface-container-low px-3 py-2.5 transition duration-fast hover:border-primary hover:bg-surface-container">
                                <span class="flex min-w-0 items-center gap-3">
                                    <span @class([
                                        'flex w-12 shrink-0 flex-col items-center justify-center rounded-md py-1 font-mono leading-tight',
                                        'border border-streak-border bg-streak-bg text-streak' => $item['category'] === 'exam',
                                        'bg-primary-fixed text-on-primary-fixed dark:bg-primary-container dark:text-on-primary-container' => $item['category'] !== 'exam',
                                    ])>
                                        <span class="text-sm font-extrabold">{{ $item['dayNum'] }}</span>
                                        <span class="text-[0.6rem] font-bold uppercase">{{ $item['monthShort'] }}</span>
                                    </span>

                                    <span class="flex min-w-0 flex-col">
                                        <span class="flex items-center gap-1.5 text-[0.7rem] font-bold text-on-surface-variant">
                                            <span class="size-2 shrink-0 rounded-full" style="background-color: {{ $item['subjectColor'] }}"></span>
                                            <span class="truncate">{{ $item['subjectName'] }}</span>
                                        </span>
                                        <span class="truncate text-sm font-bold text-on-surface group-hover:text-primary">{{ $item['title'] }}</span>
                                        <span class="flex items-center gap-1.5 text-xs text-on-surface-variant">
                                            <x-ui.icon :name="$item['icon']" size="text-sm" />
                                            <span>{{ $item['typeLabel'] }}</span>
                                            <span>·</span>
                                            <span class="font-mono">{{ $item['timeLabel'] }}</span>
                                        </span>
                                    </span>
                                </span>

                                <x-ui.badge :variant="$item['isUrgent'] ? 'streak' : ($item['category'] === 'exam' ? 'tertiary' : 'neutral')" class="shrink-0">
                                    {{ $item['statusLabel'] }}
                                </x-ui.badge>
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            {{-- 3. Próximas Aulas --}}
            @if ($lessonDays->isNotEmpty())
                <x-ui.card x-show="matchesSection('lessons')">
                    <div class="mb-sm flex items-center justify-between border-b border-surface-variant pb-sm">
                        <x-ui.card-title icon="school">{{ __('Próximas aulas') }}</x-ui.card-title>
                        <span class="font-mono text-xs text-on-surface-variant">{{ $upcomingLessons->count() }}</span>
                    </div>

                    <div class="flex flex-col gap-md">
                        @foreach ($lessonDays as $date => $dayLessons)
                            @php($firstLesson = $dayLessons->first())
                            <div class="flex flex-col gap-2">
                                <h3 class="flex items-baseline gap-2 text-xs font-bold uppercase tracking-wide text-on-surface-variant">
                                    <span @class(['text-primary' => $date === $today->format('Y-m-d')])>{{ $firstLesson['dayLabel'] }}</span>
                                    <span class="font-mono font-medium normal-case">{{ $firstLesson['dateLabel'] }}</span>
                                </h3>

                                <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                                    @foreach ($dayLessons as $lesson)
                                        <a href="{{ $lesson['url'] }}" x-show="matchesSubject({{ $lesson['subjectId'] }})"
                                            class="group flex items-center justify-between gap-sm rounded-lg border border-surface-variant bg-surface-container-low px-3 py-2.5 transition duration-fast hover:border-primary hover:bg-surface-container">
                                            <span class="flex min-w-0 items-center gap-3">
                                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-card"
                                                    style="color: {{ $lesson['subjectColor'] }}">
                                                    <x-ui.icon name="school" size="text-xl" />
                                                </span>

                                                <span class="flex min-w-0 flex-col">
                                                    <span class="flex items-center gap-1.5 text-[0.7rem] font-bold text-on-surface-variant">
                                                        <span class="size-2 shrink-0 rounded-full" style="background-color: {{ $lesson['subjectColor'] }}"></span>
                                                        <span class="truncate">{{ $lesson['subjectName'] }}</span>
                                                    </span>
                                                    <span class="truncate text-sm font-bold text-on-surface group-hover:text-primary">{{ $lesson['title'] }}</span>
                                                    <span class="font-mono text-xs text-on-surface-variant">{{ $lesson['timeLabel'] }}</span>
                                                </span>
                                            </span>

                                            <x-ui.icon name="chevron_right" size="text-lg" class="shrink-0 text-on-surface-variant" />
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            @endif

            {{-- 4. Avisos e Calendário dos Períodos (Recesso, Feriados, Semana de Provas) --}}
            @if ($events->isNotEmpty())
                <x-ui.card x-show="matchesSection('events')">
                    <div class="mb-sm flex items-center justify-between border-b border-surface-variant pb-sm">
                        <x-ui.card-title icon="event_note">{{ __('Avisos e calendário dos períodos') }}</x-ui.card-title>
                        <span class="font-mono text-xs text-on-surface-variant">{{ $events->count() }}</span>
                    </div>

                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                        @foreach ($events as $event)
                            <div @class([
                                'flex items-center justify-between gap-sm rounded-lg border border-surface-variant bg-surface-container-low px-3 py-2.5',
                                'opacity-65' => $event['isPast'],
                            ])>
                                <span class="flex min-w-0 items-center gap-3">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-md bg-primary-fixed text-on-primary-fixed dark:bg-primary-container dark:text-on-primary-container">
                                        <x-ui.icon :name="$event['icon']" size="text-xl" />
                                    </span>

                                    <span class="flex min-w-0 flex-col">
                                        <span class="truncate text-[0.7rem] font-bold text-on-surface-variant">
                                            {{ $event['institutionName'] }} · {{ $event['termName'] }}
                                        </span>
                                        <span class="truncate text-sm font-bold text-on-surface">{{ $event['title'] }}</span>
                                        <span class="flex flex-wrap items-center gap-1.5 text-xs text-on-surface-variant">
                                            <span>{{ $event['typeLabel'] }}</span>
                                            <span>·</span>
                                            <span class="font-mono">{{ $event['rangeLabel'] }}</span>
                                        </span>
                                    </span>
                                </span>

                                <x-ui.badge :variant="$event['isOngoing'] ? 'primary' : 'neutral'" class="shrink-0">
                                    {{ $event['isOngoing'] ? __('Em andamento') : ($event['isPast'] ? __('Encerrado') : __('Próximo')) }}
                                </x-ui.badge>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            @endif
        @endif
    </div>
</div>

