@props(['title', 'description' => null, 'back' => null])

{{-- Cabeçalho padrão de página: título, descrição opcional, link de voltar e ações à direita. --}}
<div {{ $attributes->class(['flex flex-col gap-sm sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="mb-1 inline-flex items-center gap-1 text-sm font-semibold text-on-surface-variant hover:text-primary">
                <x-ui.icon name="arrow_back" size="text-lg" />
                {{ __('Voltar') }}
            </a>
        @endif

        <h1 class="text-2xl font-bold text-primary">{{ $title }}</h1>

        @if ($description)
            <p class="mt-1 text-sm text-on-surface-variant">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-sm">
            {{ $actions }}
        </div>
    @endisset
</div>
