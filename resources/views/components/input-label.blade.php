@props(['value'])

<label {{ $attributes->class(['block text-sm font-semibold text-on-surface-variant']) }}>
    {{ $value ?? $slot }}
</label>
