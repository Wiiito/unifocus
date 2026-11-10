@props([
    'icon',
    'active' => false,
])

{{--
    Item da barra inferior (padrão Material 3): ícone dentro de um indicador em
    pílula + rótulo de 12px. Sem href vira <button> (ex.: o "Mais", que abre a
    folha); nesse caso o destaque acompanha o aria-expanded via variantes group-aria-*.
--}}
@php
    $tag = $attributes->has('href') ? 'a' : 'button';
@endphp

<{{ $tag }} @if ($tag === 'button') type="button" @endif {{ $attributes->class([
    'group flex min-h-12 min-w-0 flex-1 flex-col items-center justify-center gap-1 rounded-md py-0.5',
    'outline-none transition duration-fast focus-visible:ring-2 focus-visible:ring-primary',
    'text-primary' => $active,
    'text-on-surface-variant aria-expanded:text-primary' => ! $active,
]) }} @if ($active && $tag === 'a') aria-current="page" @endif>
    <span @class([
        'flex h-8 w-14 max-w-full items-center justify-center rounded-full transition-colors duration-fast',
        'bg-primary-container text-on-primary-container' => $active,
        'group-active:bg-surface-container group-aria-expanded:bg-primary-container group-aria-expanded:text-on-primary-container' => ! $active,
    ])>
        <x-ui.icon :name="$icon" :filled="$active" />
    </span>
    <span @class(['max-w-full truncate px-0.5 text-xs leading-4', 'font-bold' => $active, 'font-medium' => ! $active])>{{ $slot }}</span>
</{{ $tag }}>
