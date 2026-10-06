@php
    $isEditing = $activity->exists;
    $back = route('enrollments.show', $enrollment).'#atividades';
@endphp

<x-app-layout>
    <x-ui.page-header :title="$isEditing ? __('Editar atividade') : __('Nova atividade')" :back="$back"
        :description="$enrollment->subject()->name" />

    <x-ui.card>
        <form method="POST" class="flex flex-col gap-4"
            action="{{ $isEditing ? route('enrollments.activities.update', [$enrollment, $activity]) : route('enrollments.activities.store', $enrollment) }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-[2fr_1fr]">
                <x-ui.field :label="__('Título')" for="title">
                    <x-text-input id="title" name="title" class="block w-full" required maxlength="180" autofocus
                        :value="old('title', $activity->title)" :placeholder="__('Ex.: Prova 1')" />
                </x-ui.field>

                <x-ui.field :label="__('Tipo')" for="type">
                    <x-select-input id="type" name="type" class="block w-full"
                        :options="\App\Enums\ActivityType::options()" :selected="old('type', $activity->type?->value)" />
                </x-ui.field>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.field :label="__('Prazo')" for="due_at">
                    <x-text-input id="due_at" name="due_at" type="datetime-local" class="block w-full"
                        :value="old('due_at', $activity->due_at?->format('Y-m-d\TH:i'))" />
                </x-ui.field>

                <x-ui.field :label="__('Situação da entrega')" for="submission_status">
                    <x-select-input id="submission_status" name="submission_status" class="block w-full"
                        :options="\App\Enums\SubmissionStatus::options()" :selected="old('submission_status', $submission?->status->value ?? 'pending')" />
                </x-ui.field>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-ui.field :label="__('Valor (pontos)')" for="max_points" :hint="__('Vazio se não vale nota.')">
                    <x-text-input id="max_points" name="max_points" type="number" step="0.01" min="0" class="block w-full"
                        :value="old('max_points', $activity->max_points)" :placeholder="__('Ex.: 30')" />
                </x-ui.field>

                <x-ui.field :label="__('Nota obtida')" for="points" :hint="__('Preencha quando sair a correção.')">
                    <x-text-input id="points" name="points" type="number" step="0.01" min="0" class="block w-full"
                        :value="old('points', $gradeEntry?->points)" :placeholder="__('Ex.: 25')" />
                </x-ui.field>

                @include('enrollments.partials.weight-field', ['weight' => $activity->weight])
            </div>

            <x-ui.field :label="__('Descrição')" for="description">
                <x-textarea-input id="description" name="description" class="block w-full">{{ old('description', $activity->description) }}</x-textarea-input>
            </x-ui.field>

            <div class="flex justify-end gap-2">
                <x-ui.button :href="$back" variant="secondary">{{ __('Cancelar') }}</x-ui.button>
                <x-ui.button type="submit">{{ __('Salvar') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if ($isEditing)
        <form method="POST" action="{{ route('enrollments.activities.destroy', [$enrollment, $activity]) }}" class="flex justify-end"
            onsubmit="return confirm(@js(__('Remover esta atividade e a nota dela?')))">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-semibold text-error hover:underline">{{ __('Remover atividade') }}</button>
        </form>
    @endif
</x-app-layout>
