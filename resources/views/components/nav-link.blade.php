@props(['active'])

<a {{ $attributes->class([
    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5',
    'transition duration-fast focus:outline-none',
    'border-primary text-primary' => $active ?? false,
    'border-transparent text-on-surface-variant hover:border-outline-variant hover:text-primary focus:border-outline-variant' => ! ($active ?? false),
]) }}>
    {{ $slot }}
</a>
