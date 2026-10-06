{{-- Dados da turma pessoal: compartilhado entre matricular-se e editar a matrícula. --}}
@php
    $classGroup = $enrollment?->classGroup;
    $weekdays = [
        0 => __('Domingo'), 1 => __('Segunda'), 2 => __('Terça'), 3 => __('Quarta'),
        4 => __('Quinta'), 5 => __('Sexta'), 6 => __('Sábado'),
    ];
    $schedule = old('schedule', $classGroup?->schedule ?? []);
@endphp

<div class="flex flex-col gap-4">
    @if ($enrollment)
        <div class="flex items-center gap-2 rounded-md bg-surface-container-low px-4 py-3">
            <x-ui.subject-dot :subject="$classGroup->subject" size="size-3" />
            <span class="font-semibold text-on-surface">{{ $classGroup->subject->name }}</span>
        </div>
    @else
        <x-ui.field :label="__('Matéria')" for="subject_id"
            :hint="$subjects->isEmpty() ? __('Nenhuma matéria disponível. Vincule-se a uma instituição no seu perfil para ver o catálogo dela.') : null">
            <x-select-input id="subject_id" name="subject_id" class="block w-full" required :placeholder="__('Selecione...')">
                @foreach ($subjects->groupBy(fn ($subject) => $subject->institution?->name ?? __('Catálogo geral')) as $group => $groupSubjects)
                    <optgroup label="{{ $group }}">
                        @foreach ($groupSubjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string) old('subject_id') === (string) $subject->id)>
                                {{ $subject->name }}{{ $subject->code ? " ({$subject->code})" : '' }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </x-select-input>
        </x-ui.field>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-ui.field :label="__('Período letivo')" for="academic_term_id"
            :hint="$terms->isEmpty() ? __('Os períodos vêm da sua instituição.') : null">
            <x-select-input id="academic_term_id" name="academic_term_id" class="block w-full"
                :placeholder="__('Sem período')"
                :options="$terms->mapWithKeys(fn ($term) => [$term->id => $term->name.' — '.$term->institution->name])"
                :selected="old('academic_term_id', $classGroup?->academic_term_id)" />
        </x-ui.field>

        <x-ui.field :label="__('Turma')" for="name">
            <x-text-input id="name" name="name" class="block w-full" maxlength="80"
                :value="old('name', $classGroup?->name)" :placeholder="__('Ex.: Turma A, noturno')" />
        </x-ui.field>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-ui.field :label="__('Sala')" for="room">
            <x-text-input id="room" name="room" class="block w-full" maxlength="60" :value="old('room', $classGroup?->room)" />
        </x-ui.field>

        <x-ui.field :label="__('Total de aulas no período')" for="total_classes" :hint="__('Base para o limite de faltas.')">
            <x-text-input id="total_classes" name="total_classes" type="number" min="1" class="block w-full"
                :value="old('total_classes', $classGroup?->total_classes)" :placeholder="__('Ex.: 72')" />
        </x-ui.field>
    </div>

    {{-- Regras de aprovação: obrigatórias sem instituição; com instituição, vazio = regra dela. --}}
    <fieldset class="grid grid-cols-1 gap-4 rounded-md border border-surface-variant p-sm sm:grid-cols-3">
        <legend class="px-1 text-sm font-semibold text-on-surface-variant">{{ __('Regras de aprovação') }}</legend>

        <p class="text-xs text-on-surface-variant sm:col-span-3">
            {{ __('Com instituição, deixe em branco para usar as regras dela. Sem instituição, preencha conforme o plano de ensino.') }}
        </p>

        <x-ui.field :label="__('Pontos no período')" for="total_points">
            <x-text-input id="total_points" name="total_points" type="number" min="1" class="block w-full"
                :value="old('total_points', $classGroup?->total_points)" :placeholder="__('Ex.: 100')" />
        </x-ui.field>

        <x-ui.field :label="__('% para aprovar')" for="passing_percent">
            <x-text-input id="passing_percent" name="passing_percent" type="number" step="0.01" min="0" max="100" class="block w-full"
                :value="old('passing_percent', $classGroup?->passing_percent)" :placeholder="__('Ex.: 65')" />
        </x-ui.field>

        <x-ui.field :label="__('% máx. de faltas')" for="max_absence_percent">
            <x-text-input id="max_absence_percent" name="max_absence_percent" type="number" min="0" max="100" class="block w-full"
                :value="old('max_absence_percent', $classGroup?->max_absence_percent)" :placeholder="__('Ex.: 25')" />
        </x-ui.field>
    </fieldset>

    {{-- Horário semanal: linhas dinâmicas com Alpine; linhas sem dia são descartadas no servidor. --}}
    <fieldset x-data="{ rows: @js(array_values($schedule)) }" class="flex flex-col gap-2">
        <legend class="text-sm font-semibold text-on-surface-variant">{{ __('Horário semanal') }}</legend>

        <template x-for="(row, index) in rows" :key="index">
            <div class="grid grid-cols-[1fr_auto_auto_auto] items-center gap-2">
                <select :name="`schedule[${index}][weekday]`" x-model="row.weekday" :aria-label="'{{ __('Dia da semana') }}'"
                    class="rounded-md border-outline-variant bg-surface-container-lowest text-on-surface shadow-sm focus:border-primary focus:ring-primary">
                    @foreach ($weekdays as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input type="time" :name="`schedule[${index}][start]`" x-model="row.start" required :aria-label="'{{ __('Início') }}'"
                    class="rounded-md border-outline-variant bg-surface-container-lowest text-on-surface shadow-sm focus:border-primary focus:ring-primary">
                <input type="time" :name="`schedule[${index}][end]`" x-model="row.end" required :aria-label="'{{ __('Fim') }}'"
                    class="rounded-md border-outline-variant bg-surface-container-lowest text-on-surface shadow-sm focus:border-primary focus:ring-primary">
                <button type="button" @click="rows.splice(index, 1)" class="text-on-surface-variant hover:text-error" aria-label="{{ __('Remover horário') }}">
                    <x-ui.icon name="delete" size="text-xl" />
                </button>
            </div>
        </template>

        <button type="button" @click="rows.push({ weekday: '1', start: '19:00', end: '20:40' })"
            class="inline-flex w-fit items-center gap-1 text-sm font-semibold text-primary hover:underline">
            <x-ui.icon name="add" size="text-lg" />
            {{ __('Adicionar horário') }}
        </button>

        <x-input-error :messages="collect($errors->get('schedule.*'))->flatten()->unique()->all()" />
    </fieldset>

    @if ($enrollment)
        <x-ui.field :label="__('Situação da matrícula')" for="status"
            :hint="__('Ao concluir, o resultado final (aprovado ou reprovado) é calculado pelos seus pontos.')">
            <x-select-input id="status" name="status" class="block w-full"
                :options="\App\Enums\EnrollmentStatus::options()" :selected="old('status', $enrollment->status->value)" />
        </x-ui.field>
    @endif
</div>
