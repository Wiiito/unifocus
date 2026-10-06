<x-app-layout>
    <x-ui.page-header :title="__('Praticar')" :description="__('Questões das matérias que você está cursando.')">
        @if ($subjects->count() > 1)
            <x-slot:actions>
                <form method="GET" action="{{ route('practice.show') }}">
                    <label for="subject" class="sr-only">{{ __('Matéria') }}</label>
                    <x-select-input id="subject" name="subject" onchange="this.form.submit()" :placeholder="__('Todas as matérias')"
                        :options="$subjects->pluck('name', 'id')" :selected="$subjectId" />
                </form>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if ($lastAttempt)
        @php
            $answered = $lastAttempt->question;
            $correctOption = $answered->options->firstWhere('is_correct', true);
        @endphp

        <x-ui.card>
            <div class="mb-sm flex items-center gap-2">
                @if ($lastAttempt->is_correct === true)
                    <x-ui.badge variant="success"><x-ui.icon name="check" size="text-base" /> {{ __('Você acertou!') }}</x-ui.badge>
                @elseif ($lastAttempt->is_correct === false)
                    <x-ui.badge variant="danger"><x-ui.icon name="close" size="text-base" /> {{ __('Não foi dessa vez') }}</x-ui.badge>
                @else
                    <x-ui.badge variant="neutral">{{ __('Compare sua resposta com o gabarito') }}</x-ui.badge>
                @endif
                <span class="text-xs text-on-surface-variant">{{ $answered->subject?->name }}</span>
            </div>

            <p class="whitespace-pre-line font-semibold text-on-surface">{{ $answered->statement }}</p>

            <dl class="mt-md flex flex-col gap-sm text-sm">
                <div>
                    <dt class="text-on-surface-variant">{{ __('Sua resposta') }}</dt>
                    <dd class="font-semibold text-on-surface">
                        {{ $lastAttempt->option?->content ?? $lastAttempt->answer_text }}
                    </dd>
                </div>

                @if ($correctOption)
                    <div>
                        <dt class="text-on-surface-variant">{{ __('Resposta correta') }}</dt>
                        <dd class="font-semibold text-emerald-600 dark:text-emerald-300">{{ $correctOption->content }}</dd>
                    </div>
                @endif

                @if ($answered->explanation)
                    <div>
                        <dt class="text-on-surface-variant">{{ __('Gabarito comentado') }}</dt>
                        <dd class="whitespace-pre-line text-on-surface">{{ $answered->explanation }}</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-md flex justify-end">
                <x-ui.button :href="route('practice.show', ['subject' => $subjectId])">
                    {{ __('Próxima questão') }}
                    <x-ui.icon name="arrow_forward" size="text-lg" />
                </x-ui.button>
            </div>
        </x-ui.card>
    @elseif ($question)
        <x-ui.card>
            <div class="mb-sm flex flex-wrap items-center gap-2">
                <x-ui.badge variant="neutral">{{ $question->subject?->name }}</x-ui.badge>
                <x-ui.badge variant="tertiary">{{ $question->difficulty->label() }}</x-ui.badge>
                @if ($question->topic)
                    <span class="text-xs text-on-surface-variant">{{ $question->topic }}</span>
                @endif
            </div>

            <p class="whitespace-pre-line text-lg font-semibold text-on-surface">{{ $question->statement }}</p>

            <form method="POST" action="{{ route('practice.store', $question) }}" class="mt-md flex flex-col gap-sm">
                @csrf
                <input type="hidden" name="subject" value="{{ $subjectId }}">

                @if ($question->type->usesOptions())
                    <fieldset class="flex flex-col gap-2">
                        <legend class="sr-only">{{ __('Alternativas') }}</legend>

                        @foreach ($question->options as $option)
                            <label class="flex cursor-pointer items-start gap-sm rounded-md border border-surface-variant bg-surface-bright px-md py-sm
                                          transition duration-fast has-[:checked]:border-primary has-[:checked]:bg-primary-container/10">
                                <input type="radio" name="option_id" value="{{ $option->id }}" required
                                    class="mt-0.5 border-outline text-primary focus:ring-primary">
                                <span class="text-on-surface">
                                    @if ($option->label)
                                        <span class="font-mono font-bold text-primary">{{ $option->label }})</span>
                                    @endif
                                    {{ $option->content }}
                                </span>
                            </label>
                        @endforeach
                    </fieldset>
                    <x-input-error :messages="$errors->get('option_id')" />
                @else
                    <x-ui.field :label="__('Sua resposta')" for="answer_text" error="answer_text">
                        @if ($question->type === \App\Enums\QuestionType::Numeric)
                            <x-text-input id="answer_text" name="answer_text" inputmode="decimal" class="block w-full" required />
                        @else
                            <x-textarea-input id="answer_text" name="answer_text" rows="5" class="block w-full" required />
                        @endif
                    </x-ui.field>
                @endif

                <div class="flex justify-end">
                    <x-ui.button type="submit">{{ __('Responder') }}</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @else
        <x-ui.card>
            <x-ui.empty-state icon="psychology" :title="__('Nenhuma questão disponível')"
                :description="$subjects->isEmpty()
                    ? __('Adicione as matérias que você está cursando para receber questões delas.')
                    : __('Ainda não há questões cadastradas para as suas matérias. Volte em breve!')">
                @if ($subjects->isEmpty())
                    <x-ui.button :href="route('enrollments.create')">{{ __('Adicionar matéria') }}</x-ui.button>
                @endif
            </x-ui.empty-state>
        </x-ui.card>
    @endif
</x-app-layout>
