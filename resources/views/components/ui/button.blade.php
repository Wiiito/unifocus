@props([
    'variant' => 'primary',
    'href' => null,
])

{{-- Definição única do estilo de botão. Renderiza <a> quando recebe href. --}}
@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-full px-6 py-2.5 font-mono text-sm font-semibold'
        .' transition duration-fast active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2'
        .' focus:ring-offset-background disabled:pointer-events-none disabled:opacity-50';

    $variants = [
        'primary' => 'bg-primary text-on-primary shadow-[0_4px_14px_rgba(0,97,164,0.35)] hover:bg-primary-hover focus:ring-primary',
        'secondary' => 'border border-outline-variant bg-surface-container-low text-on-surface hover:bg-surface-container focus:ring-primary',
        'danger' => 'bg-error text-on-error hover:opacity-90 focus:ring-error',
    ];

    $classes = [$base, $variants[$variant] ?? $variants['primary']];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit'])->class($classes) }}>{{ $slot }}</button>
@endif
