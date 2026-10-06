@props(['enrollments'])

{{-- Pontos acumulados nas matérias em andamento (caches da matrícula). --}}
<x-ui.card>
    <div class="mb-md flex items-center justify-between">
        <x-ui.card-title>{{ __('Resumo de Notas') }}</x-ui.card-title>
        <a href="{{ route('report-card') }}" class="text-tertiary" aria-label="{{ __('Ver boletim') }}" title="{{ __('Ver boletim') }}">
            <x-ui.icon name="analytics" size="text-[28px]" />
        </a>
    </div>

    @if ($enrollments->isEmpty())
        <x-ui.empty-state icon="menu_book" :title="__('Nenhuma matéria em andamento')"
            :description="__('Adicione as matérias que você está cursando para acompanhar notas e faltas.')">
            <x-ui.button :href="route('enrollments.create')">{{ __('Adicionar matéria') }}</x-ui.button>
        </x-ui.empty-state>
    @else
        <ul class="flex flex-1 flex-col gap-sm" aria-label="{{ __('Notas por disciplina') }}">
            @foreach ($enrollments as $enrollment)
                @php
                    $rules = $enrollment->academicRules();
                    $points = $enrollment->final_grade;
                @endphp

                <li>
                    <a href="{{ route('enrollments.show', $enrollment) }}"
                        class="flex items-center justify-between gap-sm rounded-md border border-surface-variant bg-surface-bright px-3.5 py-2.5
                               transition duration-fast hover:bg-surface-container-low active:scale-[0.98]
                               focus:outline-none focus:ring-2 focus:ring-primary">
                        <span class="flex min-w-0 items-center gap-2.5">
                            <x-ui.subject-dot :subject="$enrollment->subject()" size="size-2" />
                            <span class="truncate text-[0.95rem] font-semibold text-on-surface">{{ $enrollment->subject()->name }}</span>
                        </span>

                        <x-ui.badge :variant="$points !== null && $rules && $points >= $rules->passingPoints() ? 'success' : ($points === null ? 'neutral' : 'primary')" class="shrink-0">
                            {{ $points === null ? '—' : \Illuminate\Support\Number::format($points, maxPrecision: 2) }}{{ $rules ? '/'.$rules->totalPoints : '' }}
                        </x-ui.badge>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</x-ui.card>
