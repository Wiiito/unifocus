@props(['enrollment', 'term' => null, 'activities'])

@php
    use App\Enums\ActivityType;
    use App\Enums\TermEventType;

    $datedActivities = $activities
        ->filter(fn ($activity): bool => $activity->due_at !== null)
        ->sortBy('due_at')
        ->values();

    $events = ($term?->events ?? collect())->sortBy('starts_on')->values();

    $activityItems = $datedActivities->map(function ($activity) use ($enrollment): array {
        $submission = $activity->submissions->first();
        $delivered = $submission?->status->isDelivered() ?? false;
        $isExam = in_array($activity->type, [ActivityType::Exam, ActivityType::Recovery], true);

        return [
            'id' => $activity->id,
            'kind' => 'activity',
            'category' => $isExam ? 'exam' : 'delivery',
            'typeLabel' => $activity->type->label(),
            'icon' => $activity->type->icon(),
            'title' => $activity->title,
            'date' => $activity->due_at->format('Y-m-d'),
            'timeLabel' => $activity->due_at->format('H:i'),
            'delivered' => $delivered,
            'overdue' => ! $delivered && $activity->isOverdue(),
            'statusLabel' => $submission?->status->label() ?? __('Pendente'),
            'url' => route('enrollments.activities.edit', [$enrollment, $activity]),
        ];
    })->values();

    $eventItems = $events->map(fn ($event): array => [
        'id' => $event->id,
        'kind' => 'event',
        'category' => 'event',
        'typeLabel' => $event->type->label(),
        'icon' => $event->type->icon(),
        'title' => $event->title,
        'startsOn' => $event->starts_on->format('Y-m-d'),
        'endsOn' => $event->ends_on->format('Y-m-d'),
        'rangeLabel' => $event->starts_on->format('d/m')
            .(! $event->ends_on->isSameDay($event->starts_on) ? ' – '.$event->ends_on->format('d/m') : ''),
    ])->values();

    $today = today();
    $initialDate = $today;

    if ($term && ! $today->betweenIncluded($term->starts_on, $term->ends_on)) {
        $nextActivity = $datedActivities->first(fn ($activity): bool => $activity->due_at->gte($today));
        $nextEvent = $events->first(fn ($event): bool => $event->ends_on->gte($today));
        $initialDate = $nextActivity?->due_at ?? $nextEvent?->starts_on ?? $term->starts_on;
    }

    $totalReminders = $datedActivities->count() + $events->count();
@endphp

<x-ui.card id="calendario" class="scroll-mt-28">
    <div x-data="subjectCalendar({
            initialYear: {{ (int) $initialDate->format('Y') }},
            initialMonth: {{ (int) $initialDate->format('n') - 1 }},
            today: @js($today->format('Y-m-d')),
            activities: @js($activityItems),
            events: @js($eventItems),
        })">

        {{-- Cabeçalho com título e alternador entre Calendário e Lembretes --}}
        <div class="mb-md flex flex-wrap items-center justify-between gap-sm">
            <x-ui.card-title icon="event_note">
                {{ $term ? __('Calendário de :term', ['term' => $term->name]) : __('Calendário da matéria') }}
            </x-ui.card-title>

            <div class="inline-flex rounded-full border border-surface-variant bg-surface-container-low p-1" role="tablist"
                aria-label="{{ __('Visualização do calendário') }}">
                <button type="button" role="tab" :aria-selected="(view === 'calendar').toString()" @click="view = 'calendar'"
                    :class="view === 'calendar'
                        ? 'bg-primary text-on-primary shadow-sm'
                        : 'text-on-surface-variant hover:text-on-surface'"
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold transition duration-fast">
                    <x-ui.icon name="calendar_month" size="text-base" />
                    <span>{{ __('Calendário') }}</span>
                </button>

                <button type="button" role="tab" :aria-selected="(view === 'list').toString()" @click="view = 'list'"
                    :class="view === 'list'
                        ? 'bg-primary text-on-primary shadow-sm'
                        : 'text-on-surface-variant hover:text-on-surface'"
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold transition duration-fast">
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
        </div>

        {{-- VISÃO 1: GRADE MENSAL DE CALENDÁRIO --}}
        <div x-show="view === 'calendar'">
            <div class="mx-auto max-w-md rounded-xl border border-surface-variant bg-surface-container-low p-3 sm:p-4">

                {{-- Barra superior: < [Mês] [Ano] > --}}
                <div class="mb-3 flex items-center justify-between gap-2">
                    <button type="button" @click="prevMonth()" aria-label="{{ __('Mês anterior') }}"
                        class="flex size-9 shrink-0 items-center justify-center rounded-full border border-surface-variant bg-surface-container-lowest text-on-surface shadow-sm transition duration-fast hover:border-primary hover:text-primary active:scale-90">
                        <x-ui.icon name="chevron_left" size="text-xl" />
                    </button>

                    <div class="flex items-center gap-1.5">
                        <label for="calendar-month" class="sr-only">{{ __('Mês') }}</label>
                        <select id="calendar-month" x-model.number="month"
                            class="rounded-lg border border-surface-variant bg-surface-container-lowest py-1.5 pl-3 pr-7 text-sm font-bold text-on-surface shadow-sm focus:border-primary focus:ring-primary">
                            <template x-for="(label, idx) in months" :key="idx">
                                <option :value="idx" :selected="idx === month" x-text="label"></option>
                            </template>
                        </select>

                        <label for="calendar-year" class="sr-only">{{ __('Ano') }}</label>
                        <select id="calendar-year" x-model.number="year"
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
                                !cell.inMonth && !cell.hasExam && !cell.hasDelivery && !cell.hasEvent
                                    ? 'bg-transparent text-outline opacity-50'
                                    : '',
                                cell.hasExam
                                    ? 'bg-streak text-white shadow-sm hover:opacity-95'
                                    : (cell.hasDelivery
                                        ? 'bg-primary text-on-primary shadow-sm hover:bg-primary-hover'
                                        : (cell.hasEvent
                                            ? 'bg-primary-fixed text-on-primary-fixed dark:bg-primary-container dark:text-on-primary-container hover:opacity-90'
                                            : (cell.inMonth
                                                ? 'border border-surface-variant bg-surface-container-lowest text-on-surface hover:border-primary'
                                                : 'border border-transparent hover:border-surface-variant'))),
                                !cell.inMonth && (cell.hasExam || cell.hasDelivery || cell.hasEvent) ? 'opacity-70' : '',
                                cell.isToday ? 'ring-2 ring-primary ring-offset-1 ring-offset-card' : '',
                                selectedDate === cell.iso ? 'ring-2 ring-on-surface dark:ring-white' : '',
                            ]">
                            <span x-text="cell.day"></span>

                            <span x-show="cell.count > 1"
                                :class="(cell.hasExam || cell.hasDelivery) ? 'bg-current opacity-85' : 'bg-primary'"
                                class="mt-0.5 size-1 rounded-full"></span>
                        </button>
                    </template>
                </div>

                {{-- Legenda das cores do tema --}}
                <div class="mt-3 flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5 border-t border-surface-variant pt-3 text-[0.7rem] font-semibold text-on-surface-variant">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-2.5 rounded-sm bg-streak"></span>
                        {{ __('Prova') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-2.5 rounded-sm bg-primary"></span>
                        {{ __('Entrega') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-2.5 rounded-sm border border-outline-variant bg-primary-fixed dark:bg-primary-container"></span>
                        {{ __('Recesso / Evento') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-2.5 rounded-sm ring-2 ring-primary"></span>
                        {{ __('Hoje') }}
                    </span>
                </div>
            </div>

            {{-- Resumo do dia clicado no calendário --}}
            <div class="mt-md">
                <template x-if="selectedDate && selectedItems.length > 0">
                    <div class="rounded-lg border border-surface-variant bg-surface-container-low p-sm">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                {{ __('Compromissos em') }} <span class="font-mono text-on-surface" x-text="selectedDateFormatted"></span>
                            </span>
                            <button type="button" @click="view = 'list'" class="text-xs font-semibold text-primary hover:underline">
                                {{ __('Ver todos os lembretes') }} →
                            </button>
                        </div>

                        <div class="flex flex-col gap-2">
                            <template x-for="item in selectedItems" :key="item.kind + '-' + item.id">
                                <div>
                                    <template x-if="item.kind === 'activity'">
                                        <a :href="item.url"
                                            class="flex items-center justify-between gap-sm rounded-md border border-surface-variant bg-surface-container-lowest px-3 py-2 text-sm transition duration-fast hover:border-primary">
                                            <span class="flex min-w-0 items-center gap-2.5">
                                                <span :class="item.category === 'exam' ? 'border border-streak-border bg-streak-bg text-streak' : 'bg-primary-fixed text-on-primary-fixed dark:bg-primary-container dark:text-on-primary-container'"
                                                    class="flex size-8 shrink-0 items-center justify-center rounded-lg">
                                                    <span class="icon text-lg" x-text="item.icon"></span>
                                                </span>
                                                <span class="flex min-w-0 flex-col">
                                                    <span class="truncate font-semibold text-on-surface" x-text="item.title"></span>
                                                    <span class="text-xs text-on-surface-variant" x-text="item.typeLabel + ' · ' + item.timeLabel + ' · ' + item.statusLabel"></span>
                                                </span>
                                            </span>
                                            <span class="icon shrink-0 text-lg text-on-surface-variant">chevron_right</span>
                                        </a>
                                    </template>

                                    <template x-if="item.kind === 'event'">
                                        <div class="flex items-center justify-between gap-sm rounded-md border border-surface-variant bg-surface-container-lowest px-3 py-2 text-sm">
                                            <span class="flex min-w-0 items-center gap-2.5">
                                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary-fixed text-on-primary-fixed dark:bg-primary-container dark:text-on-primary-container">
                                                    <span class="icon text-lg" x-text="item.icon"></span>
                                                </span>
                                                <span class="flex min-w-0 flex-col">
                                                    <span class="truncate font-semibold text-on-surface" x-text="item.title"></span>
                                                    <span class="text-xs text-on-surface-variant" x-text="item.typeLabel + ' · ' + item.rangeLabel"></span>
                                                </span>
                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="selectedDate && selectedItems.length === 0">
                    <p class="text-center text-xs text-on-surface-variant">
                        {{ __('Nenhum compromisso ou aviso registrado em') }}
                        <span class="font-mono font-semibold text-on-surface" x-text="selectedDateFormatted"></span>.
                    </p>
                </template>

                <template x-if="!selectedDate">
                    <p class="text-center text-xs text-on-surface-variant">
                        {{ __('Toque em um dia destacado para ver os detalhes ou clique em Lembretes para ver a lista completa.') }}
                    </p>
                </template>
            </div>
        </div>

        {{-- VISÃO 2: JANELA DE LEMBRETES REORGANIZADA --}}
        <div x-show="view === 'list'" x-cloak class="flex flex-col gap-md">
            @if ($datedActivities->isEmpty() && $events->isEmpty())
                <x-ui.empty-state icon="event_available" :title="__('Nenhum compromisso no calendário')"
                    :description="__('Adicione provas, trabalhos ou atividades com data de entrega para vê-los aqui.')" />
            @else
                {{-- Seção 1: Provas, trabalhos e entregas da matéria --}}
                <section class="flex flex-col gap-sm" aria-label="{{ __('Provas, trabalhos e entregas') }}">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            {{ __('Provas, trabalhos e entregas') }}
                        </h3>
                        <span class="font-mono text-xs text-on-surface-variant">{{ $datedActivities->count() }}</span>
                    </div>

                    @if ($datedActivities->isEmpty())
                        <p class="rounded-md border border-surface-variant bg-surface-container-low px-3 py-2.5 text-sm text-on-surface-variant">
                            {{ __('Nenhuma prova ou entrega com data definida nesta matéria.') }}
                        </p>
                    @else
                        <div class="flex flex-col gap-2">
                            @foreach ($datedActivities as $activity)
                                @php
                                    $submission = $activity->submissions->first();
                                    $delivered = $submission?->status->isDelivered() ?? false;
                                    $overdue = ! $delivered && $activity->isOverdue();
                                    $isExam = in_array($activity->type, [ActivityType::Exam, ActivityType::Recovery], true);
                                @endphp

                                <a href="{{ route('enrollments.activities.edit', [$enrollment, $activity]) }}"
                                    class="group flex items-center justify-between gap-sm rounded-lg border border-surface-variant bg-surface-container-low px-3 py-2.5 transition duration-fast hover:border-primary hover:bg-surface-container">
                                    <span class="flex min-w-0 items-center gap-3">
                                        <span @class([
                                            'flex w-12 shrink-0 flex-col items-center justify-center rounded-md py-1 font-mono leading-tight',
                                            'bg-error-container text-on-error-container' => $overdue,
                                            'border border-streak-border bg-streak-bg text-streak' => ! $overdue && $isExam,
                                            'bg-primary-fixed text-on-primary-fixed dark:bg-primary-container dark:text-on-primary-container' => ! $overdue && ! $isExam,
                                        ])>
                                            <span class="text-sm font-extrabold">{{ $activity->due_at->format('d') }}</span>
                                            <span class="text-[0.6rem] font-bold uppercase">{{ $activity->due_at->translatedFormat('M') }}</span>
                                        </span>

                                        <span class="flex min-w-0 flex-col">
                                            <span class="truncate text-sm font-bold text-on-surface group-hover:text-primary">
                                                {{ $activity->title }}
                                            </span>
                                            <span class="flex items-center gap-1.5 text-xs text-on-surface-variant">
                                                <x-ui.icon :name="$activity->type->icon()" size="text-sm" />
                                                <span>{{ $activity->type->label() }}</span>
                                                <span>·</span>
                                                <span class="font-mono">{{ $activity->due_at->format('H:i') }}</span>
                                            </span>
                                        </span>
                                    </span>

                                    <x-ui.badge :variant="$overdue ? 'danger' : ($delivered ? 'success' : ($isExam ? 'streak' : 'neutral'))" class="shrink-0">
                                        {{ $overdue ? __('Atrasada') : ($submission?->status->label() ?? __('Pendente')) }}
                                    </x-ui.badge>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- Seção 2: Avisos e marcos do período (recesso, feriados, semana de provas) --}}
                @if ($events->isNotEmpty())
                    <section class="flex flex-col gap-sm" aria-label="{{ __('Avisos e calendário do período') }}">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                {{ __('Avisos e calendário do período') }}
                            </h3>
                            <span class="font-mono text-xs text-on-surface-variant">{{ $events->count() }}</span>
                        </div>

                        <div class="flex flex-col gap-2">
                            @foreach ($events as $event)
                                @php
                                    $isOngoing = $today->betweenIncluded($event->starts_on, $event->ends_on);
                                    $isPast = $event->ends_on->lt($today);
                                @endphp

                                <div @class([
                                    'flex items-center justify-between gap-sm rounded-lg border border-surface-variant bg-surface-container-low px-3 py-2.5',
                                    'opacity-65' => $isPast,
                                ])>
                                    <span class="flex min-w-0 items-center gap-3">
                                        <span class="flex size-10 shrink-0 items-center justify-center rounded-md bg-primary-fixed text-on-primary-fixed dark:bg-primary-container dark:text-on-primary-container">
                                            <x-ui.icon :name="$event->type->icon()" size="text-xl" />
                                        </span>

                                        <span class="flex min-w-0 flex-col">
                                            <span class="truncate text-sm font-bold text-on-surface">{{ $event->title }}</span>
                                            <span class="flex flex-wrap items-center gap-1.5 text-xs text-on-surface-variant">
                                                <span>{{ $event->type->label() }}</span>
                                                <span>·</span>
                                                <span class="font-mono">
                                                    {{ $event->starts_on->format('d/m') }}@if (! $event->ends_on->isSameDay($event->starts_on)) – {{ $event->ends_on->format('d/m') }}@endif
                                                </span>
                                            </span>
                                        </span>
                                    </span>

                                    <x-ui.badge :variant="$isOngoing ? 'primary' : 'neutral'" class="shrink-0">
                                        {{ $isOngoing ? __('Em andamento') : ($isPast ? __('Encerrado') : __('Próximo')) }}
                                    </x-ui.badge>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endif
        </div>
    </div>
</x-ui.card>
