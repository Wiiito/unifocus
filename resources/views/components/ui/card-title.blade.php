@props(['icon' => null])

<h2 {{ $attributes->class(['flex items-center gap-2 text-xl font-bold text-primary']) }}>
    @if ($icon)
        <x-ui.icon :name="$icon" />
    @endif

    {{ $slot }}
</h2>
