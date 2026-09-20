<x-public-layout :title="$subject->name">
    <a href="{{ route('subjects.index') }}" class="inline-flex w-fit items-center gap-1 text-sm font-semibold text-on-surface-variant hover:text-primary">
        <x-ui.icon name="arrow_back" size="text-lg" />
        {{ __('Voltar ao catálogo') }}
    </a>

    <x-ui.card>
        <div class="flex items-center gap-2">
            <span class="size-4 shrink-0 rounded-full" style="background-color: {{ $subject->color ?? '#5c7cfa' }}"></span>
            <x-ui.card-title class="text-2xl">{{ $subject->name }}</x-ui.card-title>
        </div>

        <div class="mt-3 flex flex-wrap gap-1.5">
            @if ($subject->code)
                <x-ui.badge variant="neutral">{{ __('Código') }}: {{ $subject->code }}</x-ui.badge>
            @endif

            @if ($subject->credits)
                <x-ui.badge variant="neutral">{{ $subject->credits }} {{ $subject->credits === 1 ? __('crédito') : __('créditos') }}</x-ui.badge>
            @endif

            @if ($subject->workload_hours)
                <x-ui.badge variant="neutral">{{ $subject->workload_hours }}h {{ __('de carga horária') }}</x-ui.badge>
            @endif
        </div>

        @if ($subject->description)
            <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-on-surface-variant">
                {{ $subject->description }}
            </p>
        @endif
    </x-ui.card>
</x-public-layout>
