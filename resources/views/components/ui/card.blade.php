@props([
    'as' => 'article',
    'inner' => true,
])

{{-- Card neomórfico: superfície padrão de todo conteúdo da aplicação. --}}
<{{ $as }} {{ $attributes->class([
    'relative rounded-lg border border-card-border bg-card shadow-card',
    'transition-shadow duration-normal ease-normal hover:shadow-card-hover',
]) }}>
    @if ($inner)
        <div class="flex h-full flex-col p-md">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif
</{{ $as }}>
