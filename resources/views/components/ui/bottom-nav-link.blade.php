@props([
    'icon',
    'active' => false,
])

<a {{ $attributes->class([
    'flex min-w-[60px] flex-col items-center justify-center gap-0.5 rounded-md px-3 py-1.5',
    'transition duration-fast active:scale-95',
    'text-primary' => $active,
    'text-on-surface-variant' => ! $active,
]) }} @if ($active) aria-current="page" @endif>
    <x-ui.icon :name="$icon" :filled="$active" />
    <span class="text-[0.68rem] font-semibold tracking-wide">{{ $slot }}</span>
</a>
