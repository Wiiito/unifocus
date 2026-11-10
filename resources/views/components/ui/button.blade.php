@props([
    'variant' => 'primary',
    'href' => null,
])

{{-- Definição única do estilo de botão. Renderiza <a> quando recebe href. --}}
@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-full px-6 py-2.5 font-mono text-sm font-semibold'
        .' transition-all duration-normal ease-bounce'
        .' hover:-translate-y-0.5 hover:scale-[1.02] active:scale-95 active:translate-y-0'
        .' focus:outline-none focus:ring-2 focus:ring-offset-2'
        .' focus:ring-offset-background disabled:pointer-events-none disabled:opacity-50 select-none';

    $variants = [
        'primary' => 'bg-primary text-on-primary shadow-[0_4px_14px_rgba(0,97,164,0.35)] hover:bg-primary-hover hover:shadow-[0_8px_22px_rgba(0,97,164,0.45)] focus:ring-primary',
        'secondary' => 'border border-outline-variant bg-surface-container-low text-on-surface shadow-sm hover:bg-surface-container hover:border-primary/40 hover:shadow-md focus:ring-primary',
        'danger' => 'bg-error text-on-error shadow-[0_4px_14px_rgba(186,26,26,0.35)] hover:opacity-95 hover:shadow-[0_8px_22px_rgba(186,26,26,0.45)] focus:ring-error',
    ];

    $classes = [$base, $variants[$variant] ?? $variants['primary']];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit'])->class($classes) }}>{{ $slot }}</button>
@endif
