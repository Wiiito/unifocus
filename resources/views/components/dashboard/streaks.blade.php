{{-- Ofensivas de estudo (dados mockados no front). --}}
<x-ui.card>
    <div class="mb-md flex items-center justify-between">
        <x-ui.card-title>{{ __('Streaks') }}</x-ui.card-title>
        <x-ui.icon name="local_fire_department" filled size="text-[30px]" class="text-streak" />
    </div>

    <div class="flex flex-1 flex-col justify-center gap-sm">
        <div class="flex items-center justify-between rounded-md border border-surface-variant bg-surface-bright px-md py-sm
                    transition-transform duration-fast active:scale-[0.98]">
            <span class="text-[0.95rem] font-medium text-on-surface">{{ __('Diária') }}</span>
            <span class="font-mono text-xl font-extrabold text-primary">15 {{ __('dias') }}</span>
        </div>

        <div class="flex items-center justify-between rounded-md border border-surface-variant bg-surface-bright px-md py-sm
                    transition-transform duration-fast active:scale-[0.98]">
            <span class="text-[0.95rem] font-medium text-on-surface">{{ __('Com Amigos') }}</span>

            <div class="flex items-center">
                @foreach ([
                    ['name' => 'Mariana Costa', 'photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80'],
                    ['name' => 'Lucas Silva', 'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80'],
                ] as $friend)
                    <img src="{{ $friend['photo'] }}" alt="{{ $friend['name'] }}" title="{{ $friend['name'] }}"
                        class="-ml-2 size-8 rounded-full border-2 border-card bg-surface-variant object-cover first:ml-0
                               transition-transform duration-fast hover:z-10 hover:scale-110">
                @endforeach

                <div title="{{ __('Mais 2 colegas') }}"
                    class="-ml-2 flex size-8 items-center justify-center rounded-full border-2 border-card bg-tertiary-container
                           text-xs font-bold text-white">+2</div>
            </div>
        </div>
    </div>
</x-ui.card>
