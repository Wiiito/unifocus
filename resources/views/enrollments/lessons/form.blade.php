@php
    $isEditing = $lesson->exists;
    $back = route('enrollments.show', $enrollment).'#aulas';
@endphp

<x-app-layout>
    <x-ui.page-header :title="$isEditing ? __('Editar aula') : __('Registrar aula')" :back="$back"
        :description="$enrollment->subject()->name" />

    <x-ui.card>
        <form method="POST" class="flex flex-col gap-4"
            action="{{ $isEditing ? route('enrollments.lessons.update', [$enrollment, $lesson]) : route('enrollments.lessons.store', $enrollment) }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <x-ui.field :label="__('Título')" for="title">
                <x-text-input id="title" name="title" class="block w-full" required maxlength="180" autofocus
                    :value="old('title', $lesson->title)" :placeholder="__('Ex.: Aula 5 — Derivadas')" />
            </x-ui.field>

            <x-ui.field :label="__('Conteúdo')" for="topic" :hint="__('O assunto da aula. No futuro, alimenta a geração de questões.')">
                <x-text-input id="topic" name="topic" class="block w-full" maxlength="180" :value="old('topic', $lesson->topic)" />
            </x-ui.field>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-ui.field :label="__('Data e hora')" for="starts_at">
                    <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="block w-full" required
                        :value="old('starts_at', $lesson->starts_at?->format('Y-m-d\TH:i'))" />
                </x-ui.field>

                <x-ui.field :label="__('Quantidade de aulas')" for="class_count" :hint="__('Aula dupla = 2.')">
                    <x-text-input id="class_count" name="class_count" type="number" min="1" max="10" class="block w-full" required
                        :value="old('class_count', $lesson->class_count)" />
                </x-ui.field>

                <x-ui.field :label="__('Situação da aula')" for="status">
                    <x-select-input id="status" name="status" class="block w-full"
                        :options="\App\Enums\LessonStatus::options()" :selected="old('status', $lesson->status?->value)" />
                </x-ui.field>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_2fr]">
                <x-ui.field :label="__('Sua presença')" for="attendance_status">
                    <x-select-input id="attendance_status" name="attendance_status" class="block w-full"
                        :placeholder="__('Ainda não registrada')"
                        :options="\App\Enums\AttendanceStatus::options()" :selected="old('attendance_status', $attendance?->status->value)" />
                </x-ui.field>

                <x-ui.field :label="__('Justificativa')" for="justification">
                    <x-text-input id="justification" name="justification" class="block w-full" maxlength="1000"
                        :value="old('justification', $attendance?->justification)" />
                </x-ui.field>
            </div>

            <x-ui.field :label="__('Anotações')" for="description">
                <x-textarea-input id="description" name="description" class="block w-full">{{ old('description', $lesson->description) }}</x-textarea-input>
            </x-ui.field>

            <div class="flex justify-end gap-2">
                <x-ui.button :href="$back" variant="secondary">{{ __('Cancelar') }}</x-ui.button>
                <x-ui.button type="submit">{{ __('Salvar') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if ($isEditing)
        <form method="POST" action="{{ route('enrollments.lessons.destroy', [$enrollment, $lesson]) }}" class="flex justify-end"
            onsubmit="return confirm(@js(__('Remover esta aula?')))">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-semibold text-error hover:underline">{{ __('Remover aula') }}</button>
        </form>
    @endif
</x-app-layout>
