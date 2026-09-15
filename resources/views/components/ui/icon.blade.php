@props([
    'name',
    'filled' => false,
    'size' => 'text-2xl',
])

{{-- Ícone Material Symbols. O tamanho vem por classe para não conflitar no merge. --}}
<span {{ $attributes->class(['icon', 'icon-filled' => $filled, $size]) }} aria-hidden="true">{{ $name }}</span>
