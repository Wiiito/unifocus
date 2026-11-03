@props(['board', 'friends'])

{{-- Foguinho do estudante (StreakService) e dos amigos (Friendship). --}}
@php
    $streak = $board->streak;
    $shownFriends = $friends->take(3);
    $hiddenFriends = $friends->count() - $shownFriends->count();
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

        <a href="{{ route('friends.index') }}" class="flex items-center justify-between rounded-md border border-surface-variant
                  bg-surface-bright px-md py-sm transition-transform duration-fast active:scale-[0.98]">
            <span class="text-[0.95rem] font-medium text-on-surface">{{ __('Com Amigos') }}</span>

            @if ($friends->isEmpty())
                <span class="flex items-center gap-1 text-sm font-semibold text-primary">
                    <x-ui.icon name="person_add" size="text-lg" />
                    {{ __('Adicionar') }}
                </span>
            @else
                <div class="flex items-center">
                    @foreach ($shownFriends as $friend)
                        <x-friends.avatar :user="$friend" class="-ml-2 size-8 border-2 border-card first:ml-0" />
                    @endforeach

                    @if ($hiddenFriends > 0)
                        <div title="{{ trans_choice('{1} Mais :count amigo|[2,*] Mais :count amigos', $hiddenFriends) }}"
                            class="-ml-2 flex size-8 items-center justify-center rounded-full border-2 border-card bg-surface-variant
                                   text-xs font-bold text-on-surface-variant">+{{ $hiddenFriends }}</div>
                    @endif
                </div>
            @endif
        </a>
    </div>
</x-ui.card>
