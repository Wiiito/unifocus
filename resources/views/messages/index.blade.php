<x-app-layout>
    <x-ui.page-header :title="__('Mensagens')" :description="__('Converse com seus amigos.')" />

    <x-ui.card>
        @forelse ($friends as $friend)
            @if ($loop->first)
                <ul class="flex flex-col gap-sm">
            @endif

            <li>
                <a href="{{ route('messages.show', $friend->id) }}"
                    class="flex items-center justify-between gap-sm rounded-md border border-surface-variant bg-surface-bright px-md py-sm
                           transition duration-fast hover:bg-surface-container">
                    <span class="flex min-w-0 items-center gap-sm">
                        <x-friends.avatar :user="$friend" class="size-9" />
                        <span class="truncate font-medium text-on-surface">{{ $friend->name }}</span>
                    </span>

                    @if (($unread[$friend->id] ?? 0) > 0)
                        <span class="rounded-full bg-primary px-2 py-0.5 text-xs font-bold text-on-primary">{{ $unread[$friend->id] }}</span>
                    @endif
                </a>
            </li>

            @if ($loop->last)
                </ul>
            @endif
        @empty
            <x-ui.empty-state icon="chat_bubble" :title="__('Ninguém para conversar ainda')"
                :description="__('Adicione amigos para começar uma conversa.')">
                <x-ui.button :href="route('friends.index')" variant="secondary">{{ __('Ir para amigos') }}</x-ui.button>
            </x-ui.empty-state>
        @endforelse
    </x-ui.card>
</x-app-layout>
