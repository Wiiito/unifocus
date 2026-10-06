@props(['item', 'showDate' => true])

{{-- Um compromisso da agenda (prazo, aula ou marco do período). --}}
@php
    $tag = $item->url ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($item->url) href="{{ $item->url }}" @endif {{ $attributes->class([
    'flex items-start gap-sm rounded-md border p-sm transition duration-fast',
    'border-error/25 bg-error-container' => $item->isUrgent,
    'border-surface-variant bg-surface-container-low' => ! $item->isUrgent,
    'hover:bg-surface-container active:scale-[0.98]' => $item->url && ! $item->isUrgent,
]) }}>
    <span class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-full bg-card"
        @if ($item->color) style="color: {{ $item->color }}" @endif>
        <x-ui.icon :name="$item->icon" size="text-xl" />
    </span>

    <span class="flex min-w-0 flex-col">
        <span class="truncate text-[0.95rem] font-bold text-on-background">{{ $item->title }}</span>
        <span class="truncate text-xs text-on-surface-variant">{{ $item->context }}</span>
        <span @class([
            'mt-0.5 font-mono text-[0.78rem]',
            'font-bold text-on-error-container' => $item->isUrgent,
            'text-on-surface-variant' => ! $item->isUrgent,
        ])>
            @if ($showDate)
                {{ $item->startsAt->translatedFormat('D, d/m') }}
            @endif
            @unless ($item->isAllDay)
                {{ $showDate ? '•' : '' }} {{ $item->startsAt->format('H:i') }}
            @endunless
        </span>
    </span>
</{{ $tag }}>
