<x-app-layout>
    <x-ui.page-header :title="__('Editar matrícula')" :back="route('enrollments.show', $enrollment)" />

    <x-ui.card>
        <form method="POST" action="{{ route('enrollments.update', $enrollment) }}" class="flex flex-col gap-md">
            @csrf
            @method('PUT')

            @include('enrollments.partials.form')

            <div class="flex justify-end gap-2">
                <x-ui.button :href="route('enrollments.show', $enrollment)" variant="secondary">{{ __('Cancelar') }}</x-ui.button>
                <x-ui.button type="submit">{{ __('Salvar') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card>
        <div class="flex flex-col gap-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-error">{{ __('Remover matéria') }}</h2>
                <p class="text-sm text-on-surface-variant">
                    {{ __('Apaga a matrícula com todas as aulas, atividades e notas registradas. Não pode ser desfeito.') }}
                </p>
            </div>

            <form method="POST" action="{{ route('enrollments.destroy', $enrollment) }}"
                onsubmit="return confirm(@js(__('Remover esta matéria e tudo o que foi registrado nela?')))">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger">{{ __('Remover') }}</x-ui.button>
            </form>
        </div>
    </x-ui.card>
</x-app-layout>
