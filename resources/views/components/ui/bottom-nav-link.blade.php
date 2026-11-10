@props([
    'icon',
    'active' => false,
])

{{--
    Item da barra inferior: só o ícone, com um ponto indicando o destino ativo
    (o rótulo fica para leitores de tela e no title). Sem href vira <button>
    (ex.: o "Mais", que abre a folha); nesse caso o destaque acompanha o
    aria-expanded via variantes aria-* / group-aria-*.
--}}
@php
    $tag = $attributes->has('href') ? 'a' : 'button';
@endphp

<{{ $tag }} @if ($tag === 'button') type="button" @endif {{ $attributes->class([
    'relative flex min-w-0 flex-1 flex-col items-center justify-center py-2 px-1 rounded-xl',
    'transition-all duration-normal ease-bounce active:scale-90 select-none group',
    'outline-none focus-visible:ring-2 focus-visible:ring-primary',
    'hover:bg-primary/10' => ! $active,
    'text-primary font-bold' => $active,
    'text-on-surface-variant hover:text-primary aria-expanded:text-primary' => ! $active,
]) }}
   title="{{ trim($slot) }}"
   @if ($active && $tag === 'a') aria-current="page" @endif>
    <span class="relative flex items-center justify-center transition-all duration-fast group-hover:-translate-y-1 group-hover:scale-110">
        <x-ui.icon :name="$icon" :filled="$active" size="text-[1.45rem]" />
    </span>

    {{-- Indicador circular ativo da referência visual --}}
    @if ($active)
        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-primary transition-all duration-normal scale-100 animate-slide-in" aria-hidden="true"></span>
    @else
        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-transparent scale-0 group-hover:scale-75 group-hover:bg-primary/40
                     group-aria-expanded:scale-100 group-aria-expanded:bg-primary transition-all duration-fast" aria-hidden="true"></span>
    @endif

    {{-- Label acessível para leitores de tela --}}
    <span class="sr-only">{{ $slot }}</span>
</{{ $tag }}>
