<x-app-layout>
    <x-ui.page-header :title="__('Adicionar matéria')" :back="route('enrollments.index')"
        :description="__('Escolha a matéria do catálogo e informe os dados da sua turma.')" />

    <x-ui.card>
        <form method="POST" action="{{ route('enrollments.store') }}" class="flex flex-col gap-md">
            @csrf

            @include('enrollments.partials.form', ['enrollment' => null])

            <div class="flex justify-end gap-2">
                <x-ui.button :href="route('enrollments.index')" variant="secondary">{{ __('Cancelar') }}</x-ui.button>
                <x-ui.button type="submit">{{ __('Adicionar') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-app-layout>
