@props(['variant' => 'primary'])

@php
    $variants = [
        'primary' => 'bg-secondary-fixed text-on-secondary-fixed',
        'neutral' => 'bg-surface-variant text-on-surface-variant',
        'tertiary' => 'bg-tertiary-container text-white',
        'streak' => 'border border-streak-border bg-streak-bg text-streak',
        'success' => 'bg-emerald-500 text-white',
        'danger' => 'bg-error-container text-on-error-container',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center gap-1.5 rounded-full px-3 py-1 font-mono text-xs font-bold',
    $variants[$variant] ?? $variants['primary'],
]) }}>
    {{ $slot }}
</span>
