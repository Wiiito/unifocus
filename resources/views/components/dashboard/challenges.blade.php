{{-- Desafios diários. O estado vive no store Alpine, compartilhado com a sidebar. --}}
<x-ui.card x-data>
    <div class="mb-md flex items-center justify-between border-b border-surface-variant pb-sm">
        <x-ui.card-title>{{ __('Desafios Diários') }}</x-ui.card-title>

        {{-- Sintaxe de objeto: o Alpine precisa REMOVER a cor base ao concluir tudo. --}}
        <x-ui.badge variant="tertiary"
            ::class="{
                'bg-emerald-500': $store.challenges.allDone,
                'bg-tertiary-container': ! $store.challenges.allDone,
            }"
            x-text="`${$store.challenges.completed}/${$store.challenges.total} {{ __('Completos') }}`">
            0/3 {{ __('Completos') }}
        </x-ui.badge>
    </div>

    <div class="grid grid-cols-1 gap-sm md:grid-cols-3">
        <template x-for="(challenge, index) in $store.challenges.items" :key="index">
            <label class="flex cursor-pointer items-center gap-sm rounded-md border bg-surface-bright px-md py-sm
                          transition duration-fast active:scale-[0.97]"
                :class="challenge.done ? 'border-primary bg-primary-container/10' : 'border-surface-variant'">

                <input type="checkbox" x-model="challenge.done" @change="$store.challenges.persist()"
                    class="size-[22px] shrink-0 rounded-[6px] border-2 border-outline bg-surface-container-lowest
                           text-primary focus:ring-primary focus:ring-offset-0">

                <span class="flex flex-col">
                    <span class="text-[0.95rem] font-semibold transition-colors duration-fast"
                        :class="challenge.done ? 'line-through text-on-surface-variant' : 'text-on-surface'"
                        x-text="challenge.title"></span>
                    <span class="text-xs text-on-surface-variant" x-text="challenge.frequency"></span>
                </span>
            </label>
        </template>
    </div>
</x-ui.card>
