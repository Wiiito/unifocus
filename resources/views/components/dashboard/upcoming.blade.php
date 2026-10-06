@props(['items'])

{{-- Próximos compromissos: atrasados, prazos, aulas e marcos do período. --}}
<x-ui.card>
    <div class="mb-sm flex items-center justify-between">
        <x-ui.card-title icon="event_upcoming">{{ __('Próximas') }}</x-ui.card-title>
        <a href="{{ route('agenda') }}" class="text-sm font-semibold text-primary hover:underline">{{ __('Ver agenda') }}</a>
    </div>

    @if ($items->isEmpty())
        <x-ui.empty-state icon="event_available" :title="__('Nada nos próximos dias')"
            :description="__('Prazos de atividades e aulas registradas aparecem aqui.')" />
    @else
        <div class="flex flex-col gap-sm" aria-label="{{ __('Cronograma de entregas e provas') }}">
            @foreach ($items as $item)
                <x-academic.agenda-item :item="$item" />
            @endforeach
        </div>
    @endif
</x-ui.card>
