@props(['placeholder' => __('Buscar...')])

<div class="relative">
    <x-ui.icon name="search" size="text-xl" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-outline" />
    <x-text-input type="search" wire:model.live.debounce.300ms="search" :placeholder="$placeholder"
        :aria-label="$placeholder" {{ $attributes->class(['w-full pl-10 sm:w-72']) }} />
</div>
