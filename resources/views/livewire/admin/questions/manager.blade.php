@php
    use App\Enums\QuestionType;
    use App\Enums\ReviewStatus;

    $reviewVariants = [
        ReviewStatus::Approved->value => 'success',
        ReviewStatus::Pending->value => 'streak',
        ReviewStatus::Rejected->value => 'danger',
    ];
@endphp

<div class="flex flex-col gap-md">
    <x-ui.page-header :title="__('Banco de questões')" :description="__('Questões reutilizáveis usadas na prática e nos flashcards dos estudantes. Só as aprovadas aparecem para eles.')">
        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" disabled
                :title="$canGenerateWithAi ? __('Gerar com IA') : __('A geração por IA ainda não está disponível')">
                <x-ui.icon name="auto_awesome" size="text-xl" />
                {{ __('Gerar com IA') }}
            </x-ui.button>

            <x-ui.button type="button" wire:click="openCreateForm">
                <x-ui.icon name="add" size="text-xl" />
                {{ __('Nova questão') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="flex flex-col gap-sm sm:flex-row">
        <x-admin.search-input :placeholder="__('Buscar no enunciado ou tópico')" />

        <x-select-input wire:model.live="subject" :aria-label="__('Filtrar por matéria')"
            :placeholder="__('Todas as matérias')" :options="$this->subjects" />

        <x-select-input wire:model.live="status" :aria-label="__('Filtrar por moderação')"
            :placeholder="__('Qualquer moderação')" :options="ReviewStatus::options()" />
    </div>

    <x-ui.flash :message="$flashMessage" />

    <x-admin.table :paginator="$this->questions">
        <x-slot:head>
            <th class="px-4 py-3">{{ __('Enunciado') }}</th>
            <th class="px-4 py-3">{{ __('Matéria') }}</th>
            <th class="px-4 py-3">{{ __('Tipo') }}</th>
            <th class="px-4 py-3">{{ __('Uso') }}</th>
            <th class="px-4 py-3">{{ __('Moderação') }}</th>
            <th class="px-4 py-3 text-right">{{ __('Ações') }}</th>
        </x-slot:head>

        @forelse ($this->questions as $question)
            <tr wire:key="question-{{ $question->id }}" class="border-b border-card-border last:border-0">
                <td class="max-w-[360px] px-4 py-3">
                    <span class="line-clamp-2 text-on-surface">{{ $question->statement }}</span>
                    <span class="text-xs text-on-surface-variant">
                        {{ $question->difficulty->label() }}{{ $question->topic ? ' · '.$question->topic : '' }} · {{ $question->source->label() }}
                    </span>
                </td>
                <td class="px-4 py-3 text-on-surface-variant">{{ $question->subject?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-on-surface-variant">{{ $question->type->label() }}</td>
                <td class="px-4 py-3 font-mono text-on-surface-variant">
                    {{ $question->times_used }}
                    @if ($question->correct_rate !== null)
                        <span class="block text-xs">{{ \Illuminate\Support\Number::format($question->correct_rate, maxPrecision: 1) }}% {{ __('acerto') }}</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <x-ui.badge :variant="$reviewVariants[$question->review_status->value]">{{ $question->review_status->label() }}</x-ui.badge>
                </td>
                <td class="px-4 py-3">
                    <x-admin.row-actions :id="$question->id" :confirm="__('Remover esta questão do banco?')">
                        @if ($question->review_status !== ReviewStatus::Approved)
                            <button type="button" wire:click="setReviewStatus({{ $question->id }}, 'approved')" class="font-semibold text-emerald-600 hover:underline">
                                {{ __('Aprovar') }}
                            </button>
                        @endif
                        @if ($question->review_status === ReviewStatus::Pending)
                            <button type="button" wire:click="setReviewStatus({{ $question->id }}, 'rejected')" class="font-semibold text-on-surface-variant hover:underline">
                                {{ __('Rejeitar') }}
                            </button>
                        @endif
                    </x-admin.row-actions>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-10 text-center text-on-surface-variant">{{ __('Nenhuma questão encontrada.') }}</td>
            </tr>
        @endforelse
    </x-admin.table>

    @if ($showForm)
        <x-admin.form-modal :title="$form->isEditing() ? __('Editar questão') : __('Nova questão')" max-width="max-w-2xl">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.field :label="__('Matéria')" for="subjectId" error="form.subjectId">
                    <x-select-input id="subjectId" wire:model="form.subjectId" class="block w-full"
                        :placeholder="__('Selecione...')" :options="$this->subjects" />
                </x-ui.field>

                <x-ui.field :label="__('Tópico')" for="topic" error="form.topic">
                    <x-text-input id="topic" wire:model="form.topic" class="block w-full" :placeholder="__('Ex.: Derivadas')" />
                </x-ui.field>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-ui.field :label="__('Tipo')" for="type" error="form.type">
                    <x-select-input id="type" wire:model.live="form.type" class="block w-full" :options="QuestionType::options()" />
                </x-ui.field>

                <x-ui.field :label="__('Dificuldade')" for="difficulty" error="form.difficulty">
                    <x-select-input id="difficulty" wire:model="form.difficulty" class="block w-full" :options="\App\Enums\QuestionDifficulty::options()" />
                </x-ui.field>

                <x-ui.field :label="__('Moderação')" for="reviewStatus" error="form.reviewStatus">
                    <x-select-input id="reviewStatus" wire:model="form.reviewStatus" class="block w-full" :options="ReviewStatus::options()" />
                </x-ui.field>
            </div>

            <x-ui.field :label="__('Enunciado')" for="statement" error="form.statement">
                <x-textarea-input id="statement" wire:model="form.statement" rows="4" class="block w-full" />
            </x-ui.field>

            {{-- Gabarito: o formato depende do tipo da questão. --}}
            @switch($form->questionType())
                @case(QuestionType::MultipleChoice)
                    <fieldset class="flex flex-col gap-2">
                        <legend class="text-sm font-semibold text-on-surface-variant">{{ __('Alternativas (marque a correta)') }}</legend>

                        @foreach ($form->options as $index => $option)
                            <div wire:key="option-{{ $index }}" class="flex items-start gap-2">
                                <input type="radio" wire:model="form.correctOption" value="{{ $index }}" aria-label="{{ __('Alternativa correta') }}"
                                    class="mt-3 border-outline text-primary focus:ring-primary">
                                <span class="mt-2.5 font-mono font-bold text-primary">{{ chr(ord('A') + $index) }})</span>
                                <div class="flex-1">
                                    <x-text-input wire:model="form.options.{{ $index }}.content" class="block w-full" :aria-label="__('Texto da alternativa')" />
                                    <x-input-error :messages="$errors->get('form.options.'.$index.'.content')" class="mt-1" />
                                </div>
                                @if (count($form->options) > 2)
                                    <button type="button" wire:click="removeOption({{ $index }})" class="mt-2.5 text-on-surface-variant hover:text-error"
                                        aria-label="{{ __('Remover alternativa') }}">
                                        <x-ui.icon name="delete" size="text-xl" />
                                    </button>
                                @endif
                            </div>
                        @endforeach

                        <x-input-error :messages="$errors->get('form.correctOption')" />

                        @if (count($form->options) < 6)
                            <button type="button" wire:click="addOption" class="inline-flex w-fit items-center gap-1 text-sm font-semibold text-primary hover:underline">
                                <x-ui.icon name="add" size="text-lg" />
                                {{ __('Adicionar alternativa') }}
                            </button>
                        @endif
                    </fieldset>
                    @break

                @case(QuestionType::TrueFalse)
                    <x-ui.field :label="__('Resposta correta')" for="trueFalseAnswer" error="form.trueFalseAnswer">
                        <x-select-input id="trueFalseAnswer" wire:model="form.trueFalseAnswer" class="block w-full"
                            :options="['true' => __('Verdadeiro'), 'false' => __('Falso')]" />
                    </x-ui.field>
                    @break

                @case(QuestionType::Numeric)
                    <x-ui.field :label="__('Resposta correta')" for="numericAnswer" error="form.numericAnswer" :hint="__('Aceita diferença de até 0,01.')">
                        <x-text-input id="numericAnswer" wire:model="form.numericAnswer" inputmode="decimal" class="block w-full" />
                    </x-ui.field>
                    @break
            @endswitch

            <x-ui.field :label="__('Gabarito comentado')" for="explanation" error="form.explanation"
                :hint="$form->questionType() === QuestionType::Open ? __('Obrigatório em discursivas: é a referência que o estudante usa para se corrigir.') : null">
                <x-textarea-input id="explanation" wire:model="form.explanation" rows="3" class="block w-full" />
            </x-ui.field>
        </x-admin.form-modal>
    @endif
</div>
