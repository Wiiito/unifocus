@props([
    'icon',
    'active' => false,
])

{{--
    Item lateral da barra inferior flutuante: ícone (preenchido quando ativo)
    com o ponto indicador logo abaixo.
--}}
@php
    $tag = $attributes->has('href') ? 'a' : 'button';
@endphp

<{{ $tag }} @if ($tag === 'button') type="button" @endif {{ $attributes->class([
    'relative flex min-w-0 flex-1 flex-col items-center justify-center py-2 px-1 rounded-full',
    'transition-all duration-normal ease-bounce active:scale-90 select-none group',
    'outline-none focus-visible:ring-2 focus-visible:ring-primary',
    'text-on-surface dark:text-white font-bold' => $active,
    'text-on-surface-variant/80 hover:text-on-surface dark:text-on-surface-variant dark:hover:text-white' => ! $active,
]) }}
   title="{{ trim($slot) }}"
   @if ($active && $tag === 'a') aria-current="page" @endif>
    <span class="relative flex items-center justify-center transition-all duration-fast group-hover:-translate-y-0.5 group-hover:scale-110">
        <x-ui.icon :name="$icon" :filled="$active" size="text-[1.45rem]" />
    </span>

    {{-- Ponto indicador circular abaixo do ícone ativo --}}
    @if ($active)
        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-primary transition-all duration-normal scale-100 animate-slide-in" aria-hidden="true"></span>
    @else
        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-transparent scale-0 group-hover:scale-75 group-hover:bg-primary/40 transition-all duration-fast" aria-hidden="true"></span>
    @endif

    {{-- Label acessível para leitores de tela --}}
    <span class="sr-only">{{ $slot }}</span>
</{{ $tag }}>
