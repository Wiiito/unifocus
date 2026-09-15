@php
    /** Entregas e provas mockadas no front. */
    $activities = [
        ['title' => 'Prova de Física', 'due' => 'Amanhã • 08:00', 'urgent' => true],
        ['title' => 'Trabalho de BD', 'due' => 'Sexta • 23:59', 'urgent' => false],
    ];
@endphp

<x-ui.card>
    <div class="mb-sm flex items-center justify-between">
        <x-ui.card-title icon="event_upcoming">{{ __('Próximas') }}</x-ui.card-title>
    </div>

    <div class="relative flex flex-col gap-md pl-5 before:absolute before:bottom-1.5 before:left-1.5 before:top-1.5
                before:w-0.5 before:rounded-full before:bg-surface-variant"
        aria-label="{{ __('Cronograma de entregas e provas') }}">

        @foreach ($activities as $activity)
            <div class="relative">
                <span @class([
                    'absolute -left-5 top-3 size-3.5 rounded-full border-2 border-card z-10',
                    'bg-error animate-urgent-ping' => $activity['urgent'],
                    'bg-primary' => ! $activity['urgent'],
                ])></span>

                <div @class([
                    'rounded-md border p-sm transition-transform duration-fast active:scale-[0.98]',
                    'border-error/25 bg-error-container' => $activity['urgent'],
                    'border-surface-variant bg-surface-container' => ! $activity['urgent'],
                ])>
                    <h4 class="mb-0.5 text-[0.95rem] font-bold text-on-background">{{ $activity['title'] }}</h4>

                    <p @class([
                        'font-mono text-[0.78rem]',
                        'font-bold text-on-error-container' => $activity['urgent'],
                        'text-on-surface-variant' => ! $activity['urgent'],
                    ])>{{ $activity['due'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</x-ui.card>
