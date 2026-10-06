@php
    use Illuminate\Support\Number;

    $classGroup = $enrollment->classGroup;
    $subject = $classGroup->subject;
    $term = $classGroup->academicTerm;
    $rules = $standing->rules;
    $points = fn (float $value): string => Number::format($value, maxPrecision: 2);
@endphp

<x-app-layout>
    <x-ui.page-header :title="$subject->name" :back="route('enrollments.index')"
        :description="collect([$term?->name, $classGroup->name, $classGroup->room ? __('Sala').' '.$classGroup->room : null, $subject->institution?->name])->filter()->join(' · ')">
        <x-slot:actions>
            <x-ui.badge :variant="$enrollment->final_status->badgeVariant()">{{ $enrollment->final_status->label() }}</x-ui.badge>
            <x-ui.button :href="route('enrollments.edit', $enrollment)" variant="secondary">
                <x-ui.icon name="edit" size="text-lg" />
                {{ __('Editar') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Situação: pontos e faltas --}}
    <section class="grid grid-cols-1 gap-md md:grid-cols-2" aria-label="{{ __('Situação') }}">
        <x-ui.card>
            <div class="mb-sm flex items-center justify-between">
                <x-ui.card-title icon="military_tech">{{ __('Pontos') }}</x-ui.card-title>
                <span class="font-mono text-2xl font-extrabold text-primary">
                    {{ $points($standing->pointsEarned) }}@if ($rules)<span class="text-base text-on-surface-variant">/{{ $rules->totalPoints }}</span>@endif
                </span>
            </div>

            @if (! $rules)
                <p class="text-sm text-on-surface-variant">
                    {{ __('As regras de aprovação desta matéria não estão definidas: informe os pontos do período e o percentual para aprovar.') }}
                    <a href="{{ route('enrollments.edit', $enrollment) }}" class="font-semibold text-primary hover:underline">{{ __('Definir regras') }}</a>
                </p>
            @else
            <x-ui.progress :value="$standing->pointsEarned / max(1, $rules->totalPoints) * 100"
                :variant="$standing->hasReachedPassingPoints() ? 'success' : 'primary'" :label="__('Pontos obtidos')" />

            <dl class="mt-md grid grid-cols-2 gap-sm text-sm">
                <div>
                    <dt class="text-on-surface-variant">{{ __('Mínimo para aprovação') }}</dt>
                    <dd class="font-mono font-bold text-on-surface">{{ $points($standing->passingPoints()) }} ({{ $points($rules->passingPercent) }}%)</dd>
                </div>
                <div>
                    <dt class="text-on-surface-variant">{{ __('Faltam para a média') }}</dt>
                    <dd @class(['font-mono font-bold', 'text-emerald-600' => $standing->pointsNeeded() == 0, 'text-on-surface' => $standing->pointsNeeded() > 0])>
                        {{ $standing->pointsNeeded() == 0 ? __('Média atingida') : $points($standing->pointsNeeded()) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-on-surface-variant" title="{{ __('Já considerando o peso de cada nota') }}">{{ __('Pontos já distribuídos') }}</dt>
                    <dd class="font-mono font-bold text-on-surface">{{ $points($standing->pointsDistributed) }}</dd>
                </div>
                <div>
                    <dt class="text-on-surface-variant">{{ __('Aproveitamento') }}</dt>
                    <dd class="font-mono font-bold text-on-surface">
                        {{ $standing->performancePercent() === null ? '—' : $points($standing->performancePercent()).'%' }}
                    </dd>
                </div>
            </dl>

            @if ($standing->cannotReachPassingPoints() && $enrollment->final_status === \App\Enums\FinalStatus::InProgress)
                <p class="mt-sm rounded-md bg-error-container px-3 py-2 text-sm text-on-error-container">
                    {{ __('Atenção: restam :available pontos a distribuir e você precisa de :needed.', ['available' => $points($standing->pointsStillAvailable()), 'needed' => $points($standing->pointsNeeded())]) }}
                </p>
            @endif
            @endif
        </x-ui.card>

        <x-ui.card>
            <div class="mb-sm flex items-center justify-between">
                <x-ui.card-title icon="event_busy">{{ __('Faltas') }}</x-ui.card-title>
                <span @class(['font-mono text-2xl font-extrabold', 'text-error' => $standing->hasExceededAbsences(), 'text-primary' => ! $standing->hasExceededAbsences()])>
                    {{ $standing->absences }}@if ($standing->allowedAbsences() !== null)<span class="text-base text-on-surface-variant">/{{ $standing->allowedAbsences() }}</span>@endif
                </span>
            </div>

            @if ($standing->allowedAbsences() === null)
                <p class="text-sm text-on-surface-variant">
                    {{ $rules ? __('Informe o total de aulas do período para saber quantas faltas você ainda pode ter.') : __('Defina o limite de faltas e o total de aulas do período para acompanhar a frequência.') }}
                    <a href="{{ route('enrollments.edit', $enrollment) }}" class="font-semibold text-primary hover:underline">{{ __('Informar agora') }}</a>
                </p>
            @else
                <x-ui.progress :value="$standing->absences / max(1, $standing->allowedAbsences()) * 100"
                    :variant="$standing->hasExceededAbsences() ? 'danger' : ($standing->absencesLeft() <= 2 ? 'warning' : 'primary')"
                    :label="__('Faltas usadas')" />

                <dl class="mt-md grid grid-cols-2 gap-sm text-sm">
                    <div>
                        <dt class="text-on-surface-variant">{{ __('Ainda pode faltar') }}</dt>
                        <dd class="font-mono font-bold text-on-surface">{{ trans_choice(':count aula|:count aulas', $standing->absencesLeft()) }}</dd>
                    </div>
                    <div>
                        <dt class="text-on-surface-variant">{{ __('Limite') }}</dt>
                        <dd class="font-mono font-bold text-on-surface">{{ $rules->maxAbsencePercent }}% {{ __('de') }} {{ $standing->totalClasses }}</dd>
                    </div>
                </dl>
            @endif

            <p class="mt-sm text-xs text-on-surface-variant">
                {{ trans_choice(':count aula registrada|:count aulas registradas', $standing->classesLogged) }}
            </p>
        </x-ui.card>
    </section>

    {{-- Calendário do período (semana de provas, feriados...) --}}
    @if ($term?->events->isNotEmpty())
        <x-ui.card>
            <x-ui.card-title icon="event_note" class="mb-sm">{{ __('Calendário de :term', ['term' => $term->name]) }}</x-ui.card-title>

            <ul class="flex flex-wrap gap-2">
                @foreach ($term->events as $event)
                    <li class="rounded-md border border-surface-variant bg-surface-container-low px-3 py-2 text-sm">
                        <span class="font-semibold text-on-surface">{{ $event->title }}</span>
                        <span class="block font-mono text-xs text-on-surface-variant">
                            {{ $event->starts_on->format('d/m') }}@if (! $event->ends_on->isSameDay($event->starts_on)) – {{ $event->ends_on->format('d/m') }}@endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    {{-- Atividades --}}
    <x-ui.card id="atividades" class="scroll-mt-28">
        <div class="mb-sm flex items-center justify-between gap-sm">
            <x-ui.card-title icon="assignment">{{ __('Atividades e provas') }}</x-ui.card-title>
            <x-ui.button :href="route('enrollments.activities.create', $enrollment)" variant="secondary">
                <x-ui.icon name="add" size="text-lg" />
                {{ __('Nova') }}
            </x-ui.button>
        </div>

        @forelse ($activities as $activity)
            @php
                $submission = $activity->submissions->first();
                $grade = $activity->gradeEntries->first();
                $delivered = $submission?->status->isDelivered() ?? false;
            @endphp

            <a href="{{ route('enrollments.activities.edit', [$enrollment, $activity]) }}"
                class="flex items-center justify-between gap-sm border-b border-surface-variant py-sm last:border-0 hover:bg-surface-container-low">
                <span class="flex min-w-0 items-center gap-sm">
                    <x-ui.icon :name="$activity->type->icon()" size="text-xl" class="text-outline" />
                    <span class="flex min-w-0 flex-col">
                        <span class="truncate font-semibold text-on-surface">{{ $activity->title }}</span>
                        <span @class(['text-xs', 'font-bold text-error' => ! $delivered && $activity->isOverdue(), 'text-on-surface-variant' => $delivered || ! $activity->isOverdue()])>
                            {{ $activity->type->label() }}
                            @if ($activity->due_at)
                                · {{ $activity->due_at->translatedFormat('d/m H:i') }}
                            @endif
                            · {{ $submission?->status->label() ?? __('Pendente') }}
                        </span>
                    </span>
                </span>

                @if ($activity->isGraded())
                    <span class="flex shrink-0 items-center gap-1.5">
                        @include('enrollments.partials.weight-badge', ['weight' => $activity->weight])
                        <x-ui.badge :variant="$grade ? 'primary' : 'neutral'">
                            {{ $grade ? $points($grade->points) : '—' }}/{{ $points($activity->max_points) }}
                        </x-ui.badge>
                    </span>
                @endif
            </a>
        @empty
            <x-ui.empty-state icon="assignment" :title="__('Nenhuma atividade registrada')"
                :description="__('Registre provas, trabalhos e tarefas para acompanhar prazos e pontos.')" />
        @endforelse
    </x-ui.card>

    {{-- Notas avulsas --}}
    <x-ui.card id="notas" class="scroll-mt-28">
        <div class="mb-sm flex items-center justify-between gap-sm">
            <x-ui.card-title icon="grading">{{ __('Notas avulsas') }}</x-ui.card-title>
            <x-ui.button :href="route('enrollments.grade-entries.create', $enrollment)" variant="secondary">
                <x-ui.icon name="add" size="text-lg" />
                {{ __('Lançar') }}
            </x-ui.button>
        </div>

        @forelse ($manualGrades as $grade)
            <a href="{{ route('enrollments.grade-entries.edit', [$enrollment, $grade]) }}"
                class="flex items-center justify-between gap-sm border-b border-surface-variant py-sm last:border-0 hover:bg-surface-container-low">
                <span class="flex flex-col">
                    <span class="font-semibold text-on-surface">{{ $grade->label }}</span>
                    <span class="text-xs text-on-surface-variant">{{ $grade->kind->label() }} · {{ $grade->recorded_at->format('d/m/Y') }}</span>
                </span>
                <span class="flex shrink-0 items-center gap-1.5">
                    @include('enrollments.partials.weight-badge', ['weight' => $grade->weight])
                    <x-ui.badge>{{ $points($grade->points) }}/{{ $points($grade->max_points) }}</x-ui.badge>
                </span>
            </a>
        @empty
            <p class="text-sm text-on-surface-variant">
                {{ __('Notas sem atividade cadastrada (participação, prova lançada direto...) entram aqui.') }}
            </p>
        @endforelse
    </x-ui.card>

    {{-- Aulas e presença --}}
    <x-ui.card id="aulas" class="scroll-mt-28">
        <div class="mb-sm flex items-center justify-between gap-sm">
            <x-ui.card-title icon="school">{{ __('Aulas e presença') }}</x-ui.card-title>
            <x-ui.button :href="route('enrollments.lessons.create', $enrollment)" variant="secondary">
                <x-ui.icon name="add" size="text-lg" />
                {{ __('Registrar aula') }}
            </x-ui.button>
        </div>

        @forelse ($lessons as $lesson)
            @php($attendance = $lesson->attendances->first())

            <a href="{{ route('enrollments.lessons.edit', [$enrollment, $lesson]) }}"
                class="flex items-center justify-between gap-sm border-b border-surface-variant py-sm last:border-0 hover:bg-surface-container-low">
                <span class="flex min-w-0 flex-col">
                    <span class="truncate font-semibold text-on-surface">{{ $lesson->title }}</span>
                    <span class="text-xs text-on-surface-variant">
                        {{ $lesson->starts_at->translatedFormat('D, d/m · H:i') }}
                        · {{ trans_choice(':count aula|:count aulas', $lesson->class_count) }}
                        @if ($lesson->status === \App\Enums\LessonStatus::Canceled)
                            · {{ $lesson->status->label() }}
                        @endif
                    </span>
                </span>

                @if ($attendance)
                    <x-ui.badge :variant="$attendance->status->countsAsAbsence() ? 'danger' : 'neutral'" class="shrink-0">
                        {{ $attendance->status->label() }}
                    </x-ui.badge>
                @endif
            </a>
        @empty
            <x-ui.empty-state icon="school" :title="__('Nenhuma aula registrada')"
                :description="__('Registre as aulas e marque suas faltas para acompanhar a frequência.')" />
        @endforelse
    </x-ui.card>
</x-app-layout>
