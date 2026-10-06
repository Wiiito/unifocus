@props([
    'label',
    'for',
    'error' => null,
    'hint' => null,
])

{{-- Campo de formulário: rótulo + controle (slot) + dica + erro de validação. --}}
<div {{ $attributes }}>
    <x-input-label :for="$for" :value="$label" />

    <div class="mt-1">
        {{ $slot }}
    </div>

    @if ($hint)
        <p class="mt-1 text-xs text-on-surface-variant">{{ $hint }}</p>
    @endif

    <x-input-error :messages="$errors->get($error ?? $for)" class="mt-1" />
</div>
