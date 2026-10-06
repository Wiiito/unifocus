@props(['label', 'value', 'icon', 'hint' => null])

{{-- Número de destaque do dashboard. --}}
<x-ui.card {{ $attributes }}>
    <div class="flex items-start justify-between gap-sm">
        <span class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">{{ $label }}</span>
        <x-ui.icon :name="$icon" size="text-[22px]" class="text-tertiary" />
    </div>

    <span class="mt-1 font-mono text-3xl font-extrabold text-primary">{{ $value }}</span>

    @if ($hint)
        <span class="mt-1 text-xs text-on-surface-variant">{{ $hint }}</span>
    @endif
</x-ui.card>
