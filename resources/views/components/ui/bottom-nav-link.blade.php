@props([
    'icon',
    'active' => false,
])

<a {{ $attributes->class([
    'flex min-w-0 flex-1 flex-col items-center justify-center gap-0.5 rounded-md px-0.5 py-1.5',
    'transition duration-fast active:scale-95',
    'text-primary' => $active,
    'text-on-surface-variant' => ! $active,
]) }} @if ($active) aria-current="page" @endif>
    <x-ui.icon :name="$icon" :filled="$active" />
    <span class="max-w-full truncate text-[0.62rem] font-semibold">{{ $slot }}</span>
</a>
