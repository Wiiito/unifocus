@props(['enrollment'])

{{-- Resumo de uma matrícula a partir dos caches: pontos, faltas e situação. --}}
@php
    $subject = $enrollment->subject();
    $rules = $enrollment->academicRules();
    $points = $enrollment->final_grade ?? 0;
    $allowedAbsences = $enrollment->allowedAbsences();
@endphp

<x-ui.card as="a" href="{{ route('enrollments.show', $enrollment) }}" {{ $attributes->class(['group']) }}>
    <div class="flex items-start justify-between gap-sm">
        <div class="flex min-w-0 items-center gap-2">
            <x-ui.subject-dot :subject="$subject" size="size-3" />
            <h3 class="truncate text-lg font-bold text-primary group-hover:underline">{{ $subject->name }}</h3>
        </div>

        <x-ui.badge :variant="$enrollment->final_status->badgeVariant()" class="shrink-0">
            {{ $enrollment->final_status->label() }}
        </x-ui.badge>
    </div>

    <p class="mt-1 text-xs text-on-surface-variant">
        {{ collect([$enrollment->classGroup->academicTerm?->name, $enrollment->classGroup->name, $subject->institution?->name])->filter()->join(' · ') }}
    </p>

    <div class="mt-md flex flex-col gap-sm">
        @if ($rules)
            <div>
                <div class="mb-1 flex items-center justify-between text-xs font-semibold text-on-surface-variant">
                    <span>{{ __('Pontos') }}</span>
                    <span class="font-mono text-on-surface">
                        {{ \Illuminate\Support\Number::format($points, maxPrecision: 2) }}/{{ $rules->totalPoints }}
                        <span class="text-on-surface-variant">({{ __('mín.') }} {{ \Illuminate\Support\Number::format($rules->passingPoints(), maxPrecision: 2) }})</span>
                    </span>
                </div>
                <x-ui.progress :value="$points / max(1, $rules->totalPoints) * 100"
                    :variant="$points >= $rules->passingPoints() ? 'success' : 'primary'" :label="__('Pontos')" />
            </div>
        @else
            <p class="flex items-center gap-1 text-xs font-semibold text-on-surface-variant">
                <x-ui.icon name="info" size="text-base" />
                {{ __(':points pontos · defina as regras de aprovação', ['points' => \Illuminate\Support\Number::format($points, maxPrecision: 2)]) }}
            </p>
        @endif

        <div class="flex items-center justify-between text-xs font-semibold text-on-surface-variant">
            <span class="flex items-center gap-1">
                <x-ui.icon name="event_busy" size="text-base" />
                {{ __('Faltas') }}
            </span>
            <span @class([
                'font-mono',
                'text-error' => $allowedAbsences !== null && $enrollment->absence_count > $allowedAbsences,
                'text-on-surface' => $allowedAbsences === null || $enrollment->absence_count <= $allowedAbsences,
            ])>
                {{ $enrollment->absence_count }}{{ $allowedAbsences !== null ? '/'.$allowedAbsences : '' }}
            </span>
        </div>
    </div>
</x-ui.card>
