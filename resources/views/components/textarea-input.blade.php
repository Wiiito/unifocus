@props(['disabled' => false])

<textarea @disabled($disabled) {{ $attributes->merge(['rows' => 3])->class([
    'rounded-md border-outline-variant bg-surface-container-lowest text-on-surface',
    'placeholder:text-on-surface-variant/60 shadow-sm',
    'transition duration-fast focus:border-primary focus:ring-primary disabled:opacity-60',
]) }}>{{ $slot }}</textarea>
