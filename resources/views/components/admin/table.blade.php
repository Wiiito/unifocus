@props(['paginator' => null])

{{-- Tabela padrão do painel: cabeçalho no slot "head", linhas no slot padrão. --}}
<x-ui.card :inner="false" {{ $attributes }}>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-card-border text-xs font-bold uppercase tracking-wide text-on-surface-variant">
                <tr>{{ $head }}</tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</x-ui.card>

@if ($paginator?->hasPages())
    <div>{{ $paginator->links() }}</div>
@endif
