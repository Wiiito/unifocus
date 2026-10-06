@php
    $isEditing = $gradeEntry->exists;
    $back = route('enrollments.show', $enrollment).'#notas';
@endphp

<x-app-layout>
    <x-ui.page-header :title="$isEditing ? __('Editar nota') : __('Lançar nota')" :back="$back"
        :description="$enrollment->subject()->name" />

    <x-ui.card>
        <form method="POST" class="flex flex-col gap-4"
            action="{{ $isEditing ? route('enrollments.grade-entries.update', [$enrollment, $gradeEntry]) : route('enrollments.grade-entries.store', $enrollment) }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-[2fr_1fr]">
                <x-ui.field :label="__('Descrição')" for="label">
                    <x-text-input id="label" name="label" class="block w-full" required maxlength="120" autofocus
                        :value="old('label', $gradeEntry->label)" :placeholder="__('Ex.: Participação')" />
                </x-ui.field>

                <x-ui.field :label="__('Tipo')" for="kind">
                    <x-select-input id="kind" name="kind" class="block w-full" :selected="old('kind', $gradeEntry->kind?->value)"
                        :options="collect(\App\Enums\GradeKind::options())->only(\App\Http\Requests\Enrollments\GradeEntryRequest::STANDALONE_KINDS)" />
                </x-ui.field>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-ui.field :label="__('Nota obtida')" for="points">
                    <x-text-input id="points" name="points" type="number" step="0.01" min="0" class="block w-full" required
                        :value="old('points', $gradeEntry->points)" />
                </x-ui.field>

                <x-ui.field :label="__('Valor (pontos)')" for="max_points">
                    <x-text-input id="max_points" name="max_points" type="number" step="0.01" min="0" class="block w-full" required
                        :value="old('max_points', $gradeEntry->max_points)" />
                </x-ui.field>

                @include('enrollments.partials.weight-field', ['weight' => $gradeEntry->weight])
            </div>

            <div class="flex justify-end gap-2">
                <x-ui.button :href="$back" variant="secondary">{{ __('Cancelar') }}</x-ui.button>
                <x-ui.button type="submit">{{ __('Salvar') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if ($isEditing)
        <form method="POST" action="{{ route('enrollments.grade-entries.destroy', [$enrollment, $gradeEntry]) }}" class="flex justify-end"
            onsubmit="return confirm(@js(__('Remover esta nota?')))">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-semibold text-error hover:underline">{{ __('Remover nota') }}</button>
        </form>
    @endif
</x-app-layout>
