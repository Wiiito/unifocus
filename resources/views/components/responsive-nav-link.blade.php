@props(['active'])

<a {{ $attributes->class([
    'block w-full ps-3 pe-4 py-2 border-l-4 text-start text-base font-medium',
    'transition duration-fast focus:outline-none',
    'border-primary bg-primary-container/10 text-primary' => $active ?? false,
    'border-transparent text-on-surface-variant hover:border-outline-variant hover:bg-surface-container-low hover:text-primary' => ! ($active ?? false),
]) }}>
    {{ $slot }}
</a>
