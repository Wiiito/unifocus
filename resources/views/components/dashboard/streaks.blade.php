@props(['board'])

{{-- Foguinho do estudante (StreakService). "Com Amigos" segue mockado até existir a amizade. --}}
@php
    $streak = $board->streak;
@endphp

<x-ui.card>
    <div class="mb-md flex items-center justify-between">
        <x-ui.card-title>{{ __('Streaks') }}</x-ui.card-title>
        <x-ui.icon name="local_fire_department" filled size="text-[30px]"
            :class="$board->streakCount() > 0 ? 'text-streak' : 'text-outline'" />
    </div>

    <div class="flex flex-1 flex-col justify-center gap-sm">
        <div class="flex items-center justify-between rounded-md border border-surface-variant bg-surface-bright px-md py-sm
                    transition-transform duration-fast active:scale-[0.98]">
            <span class="flex flex-col">
                <span class="text-[0.95rem] font-medium text-on-surface">{{ __('Diária') }}</span>
                <span class="text-xs text-on-surface-variant">
                    @if ($board->isComplete())
                        {{ __('Desafios de hoje concluídos') }}
                    @elseif ($board->streakCount() > 0)
                        {{ __('Conclua os desafios de hoje para manter') }}
                    @else
                        {{ __('Conclua os desafios de hoje para acender') }}
                    @endif
                </span>
            </span>
            <span class="font-mono text-xl font-extrabold text-primary">
                {{ trans_choice('{0} 0 dias|{1} :count dia|[2,*] :count dias', $board->streakCount()) }}
            </span>
        </div>

        <div class="flex items-center justify-between rounded-md border border-surface-variant bg-surface-bright px-md py-sm">
            <span class="text-[0.95rem] font-medium text-on-surface">{{ __('Recorde') }}</span>
            <span class="font-mono text-base font-bold text-on-surface-variant">
                {{ trans_choice('{0} 0 dias|{1} :count dia|[2,*] :count dias', $streak->longest_count) }}
            </span>
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
