@props([
    'icon',
    'active' => false,
])

<a {{ $attributes->class([
    'flex items-center gap-sm rounded-md px-3.5 py-2.5 text-[0.95rem] font-semibold transition duration-fast',
    'bg-primary-container text-on-primary-container shadow-[0_4px_14px_rgba(33,150,243,0.25)]' => $active,
    'text-on-surface-variant hover:bg-surface-container-low hover:text-primary' => ! $active,
]) }} @if ($active) aria-current="page" @endif>
    <x-ui.icon :name="$icon" :filled="$active" size="text-[22px]" :class="$active ? 'text-on-primary-container' : 'text-outline'" />
    <span>{{ $slot }}</span>
</a>
