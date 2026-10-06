@props(['icon' => 'inbox', 'title', 'description' => null])

<div {{ $attributes->class(['flex flex-col items-center gap-2 px-md py-lg text-center']) }}>
    <span class="flex size-14 items-center justify-center rounded-full bg-surface-container-low text-outline">
        <x-ui.icon :name="$icon" size="text-3xl" />
    </span>

    <p class="font-semibold text-on-surface">{{ $title }}</p>

    @if ($description)
        <p class="max-w-sm text-sm text-on-surface-variant">{{ $description }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-2">{{ $slot }}</div>
    @endif
</div>
