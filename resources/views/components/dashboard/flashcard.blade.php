@props(['deck'])

{{-- Flashcard 3D da revisão diária, alimentado pelo banco de questões. --}}
@if ($deck->isEmpty())
    <x-ui.card>
        <x-ui.empty-state icon="style" :title="__('Revisão diária')"
            :description="__('Quando houver questões cadastradas para as suas matérias, elas aparecem aqui como flashcards.')">
            <x-ui.button :href="route('practice.show')" variant="secondary">{{ __('Ir para a prática') }}</x-ui.button>
        </x-ui.empty-state>
    </x-ui.card>
@else
    @php
        $cards = $deck->map(fn ($question) => [
            'topic' => $question->subject?->name ?? __('Revisão'),
            'tag' => __('Flash Card').' • '.$question->difficulty->label(),
            'question' => $question->statement,
            'answer' => collect([$question->correctOption?->content, $question->explanation])->filter()->join(' — '),
        ])->values();
    @endphp

    <article x-data="flashcard(@js($cards))" @click="flip()" title="{{ __('Clique para virar o card') }}"
        class="perspective cursor-pointer select-none">

        <div class="preserve-3d relative h-full min-h-[250px] w-full rounded-lg text-center transition-transform duration-[600ms] ease-normal"
            :class="flipped && 'rotate-y-180'">

            {{-- Frente: pergunta --}}
            <div class="backface-hidden absolute inset-0 flex flex-col items-center justify-center rounded-lg border border-card-border
                        bg-card bg-gradient-to-br from-[rgba(33,150,243,0.06)] to-[rgba(0,97,164,0.02)] px-md py-lg shadow-card">
                <span class="mb-sm rounded-full bg-tertiary-fixed px-3 py-1 font-mono text-xs font-bold uppercase tracking-[0.12em] text-tertiary"
                    x-text="card.tag">{{ $cards[0]['tag'] }}</span>

                <h3 class="mb-sm text-[1.45rem] font-bold text-primary md:text-[1.85rem]" x-text="card.topic">{{ $cards[0]['topic'] }}</h3>

                <p class="mx-auto mb-md line-clamp-4 max-w-[440px] shrink-0 leading-relaxed text-on-surface-variant" x-text="card.question">
                    {{ $cards[0]['question'] }}
                </p>

                <x-primary-button type="button" @click.stop="reveal()">
                    <x-ui.icon name="play_arrow" filled size="text-xl" />
                    <span>{{ __('Ver resposta') }}</span>
                </x-primary-button>

                <span class="mt-2.5 flex items-center gap-1 text-xs text-on-surface-variant">
                    <x-ui.icon name="touch_app" size="text-base" />
                    {{ __('Toque no card para ver a resposta') }}
                </span>
            </div>

            {{-- Verso: resposta --}}
            <div class="backface-hidden rotate-y-180 absolute inset-0 flex flex-col items-center justify-center rounded-lg
                        border border-white/20 bg-gradient-to-br from-primary to-secondary px-md py-lg text-on-primary shadow-card-hover">
                <span class="mb-sm rounded-full bg-white/20 px-3 py-1 font-mono text-xs font-bold uppercase tracking-[0.12em] text-white">
                    {{ __('Resposta • Conceito Chave') }}
                </span>

                <h3 class="mb-sm text-[1.45rem] font-bold text-white md:text-[1.85rem]" x-text="card.topic"></h3>

                <p class="mx-auto mb-md line-clamp-5 max-w-[440px] shrink-0 leading-relaxed text-white/90" x-text="card.answer"></p>

                <x-secondary-button @click.stop="next()" class="!border-white/30 !bg-white !text-primary hover:!bg-white/90">
                    <x-ui.icon name="sync" size="text-xl" />
                    <span>{{ __('Próximo Card') }}</span>
                </x-secondary-button>
            </div>
        </div>
    </article>
@endif
