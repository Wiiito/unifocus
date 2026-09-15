<a {{ $attributes->class([
    'block w-full px-4 py-2 text-start text-sm leading-5 text-on-surface-variant',
    'transition duration-fast hover:bg-surface-container-low hover:text-primary',
    'focus:bg-surface-container-low focus:outline-none',
]) }}>{{ $slot }}</a>
