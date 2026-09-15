@props(['status'])

@if ($status)
    <div {{ $attributes->class([
        'rounded-md border border-glass bg-surface-container-low px-4 py-2 text-sm font-medium text-primary',
    ]) }}>
        {{ $status }}
    </div>
@endif
