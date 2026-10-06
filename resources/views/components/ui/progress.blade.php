@props([
    'value' => 0,
    'variant' => 'primary',
    'label' => null,
])

{{-- Barra de progresso de 0 a 100. --}}
@php
    $percent = max(0, min(100, (float) $value));

    $variants = [
        'primary' => 'bg-gradient-to-r from-primary-container to-primary',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-streak',
        'danger' => 'bg-error',
    ];
@endphp

<div {{ $attributes->class(['h-2 w-full overflow-hidden rounded-full bg-surface-variant']) }}
    role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($percent) }}"
    @if ($label) aria-label="{{ $label }}" @endif>
    <div class="h-full rounded-full transition-[width] duration-700 ease-normal {{ $variants[$variant] ?? $variants['primary'] }}"
        style="width: {{ $percent }}%"></div>
</div>
