<x-public-layout :title="__('Catálogo de matérias')">
    <div>
        <h1 class="text-2xl font-bold text-primary">{{ __('Catálogo de matérias') }}</h1>
        <p class="mt-1 text-sm text-on-surface-variant">
            {{ __('Conheça as matérias cadastradas na plataforma.') }}
        </p>
    </div>

    @if ($subjects->isEmpty())
        <x-ui.card>
            <p class="text-sm text-on-surface-variant">{{ __('Nenhuma matéria cadastrada ainda.') }}</p>
        </x-ui.card>
    @else
        <div class="grid grid-cols-1 gap-md sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($subjects as $subject)
                <x-ui.card as="a" href="{{ route('subjects.show', $subject) }}">
                    <div class="flex items-center gap-2">
                        <span class="size-3 shrink-0 rounded-full" style="background-color: {{ $subject->color ?? '#5c7cfa' }}"></span>
                        <x-ui.card-title>{{ $subject->name }}</x-ui.card-title>
                    </div>

                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @if ($subject->code)
                            <x-ui.badge variant="neutral">{{ $subject->code }}</x-ui.badge>
                        @endif

                        @if ($subject->credits)
                            <x-ui.badge variant="neutral">
                                {{ $subject->credits }} {{ $subject->credits === 1 ? __('crédito') : __('créditos') }}
                            </x-ui.badge>
                        @endif
                    </div>

                    @if ($subject->description)
                        <p class="mt-2 line-clamp-2 text-sm text-on-surface-variant">{{ $subject->description }}</p>
                    @endif
                </x-ui.card>
            @endforeach
        </div>

        <div>
            {{ $subjects->links() }}
        </div>
    @endif
</x-public-layout>
