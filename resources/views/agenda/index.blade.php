<x-app-layout>
    <x-ui.page-header :title="__('Agenda')"
        :description="__('Prazos, aulas e o calendário dos seus períodos nos próximos :days dias.', ['days' => $daysAhead])" />

    @if ($overdue->isNotEmpty())
        <x-ui.card>
            <x-ui.card-title icon="warning" class="mb-sm !text-error">{{ __('Atrasadas') }}</x-ui.card-title>

            <div class="grid grid-cols-1 gap-sm md:grid-cols-2">
                @foreach ($overdue as $item)
                    <x-academic.agenda-item :item="$item" />
                @endforeach
            </div>
        </x-ui.card>
    @endif

    @forelse ($days as $date => $items)
        @php($day = \Illuminate\Support\Carbon::parse($date))

        <section class="flex flex-col gap-sm" aria-label="{{ $day->translatedFormat('l, d \d\e F') }}">
            <h2 class="flex items-baseline gap-2 text-sm font-bold uppercase tracking-wide text-on-surface-variant">
                <span @class(['text-primary' => $day->isToday()])>
                    {{ $day->isToday() ? __('Hoje') : ($day->isTomorrow() ? __('Amanhã') : $day->translatedFormat('l')) }}
                </span>
                <span class="font-mono font-medium normal-case">{{ $day->format('d/m') }}</span>
            </h2>

            <div class="grid grid-cols-1 gap-sm md:grid-cols-2">
                @foreach ($items as $item)
                    <x-academic.agenda-item :item="$item" :show-date="false" />
                @endforeach
            </div>
        </section>
    @empty
        <x-ui.card>
            <x-ui.empty-state icon="event_available" :title="__('Agenda livre')"
                :description="__('Nada registrado para os próximos dias. Cadastre atividades com prazo e aulas nas suas matérias.')">
                <x-ui.button :href="route('enrollments.index')" variant="secondary">{{ __('Ir para matérias') }}</x-ui.button>
            </x-ui.empty-state>
        </x-ui.card>
    @endforelse
</x-app-layout>
