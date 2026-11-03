<x-app-layout>
    <x-ui.page-header :title="__('Amigos')"
        :description="__('Adicione colegas pelo e-mail e acompanhe o foguinho de quem estuda com você.')" />

    <x-ui.flash />

    <x-ui.card>
        <x-ui.card-title icon="person_add" class="mb-sm">{{ __('Adicionar amigo') }}</x-ui.card-title>

        <form method="POST" action="{{ route('friends.store') }}" class="flex flex-col gap-sm sm:flex-row sm:items-start">
            @csrf

            <x-ui.field :label="__('E-mail do colega')" for="email" class="flex-1">
                <x-text-input id="email" name="email" type="email" class="block w-full" :value="old('email')"
                    placeholder="colega@email.com" required />
            </x-ui.field>

            <x-ui.button class="sm:mt-[1.65rem]">{{ __('Enviar pedido') }}</x-ui.button>
        </form>
    </x-ui.card>

    @if ($received->isNotEmpty())
        <x-ui.card>
            <x-ui.card-title icon="mail" class="mb-sm">{{ __('Pedidos recebidos') }}</x-ui.card-title>

            <ul class="flex flex-col gap-sm">
                @foreach ($received as $request)
                    <li class="flex items-center justify-between gap-sm rounded-md border border-surface-variant bg-surface-bright px-md py-sm">
                        <span class="flex min-w-0 items-center gap-sm">
                            <x-friends.avatar :user="$request->requester" class="size-9" />
                            <span class="truncate font-medium text-on-surface">{{ $request->requester->name }}</span>
                        </span>

                        <span class="flex shrink-0 items-center gap-2">
                            <form method="POST" action="{{ route('friends.update', $request->id) }}">
                                @csrf
                                @method('PATCH')
                                <x-ui.button class="!px-4 !py-1.5">{{ __('Aceitar') }}</x-ui.button>
                            </form>

                            <form method="POST" action="{{ route('friends.destroy', $request->id) }}">
                                @csrf
                                @method('DELETE')
                                <x-ui.button variant="secondary" class="!px-4 !py-1.5">{{ __('Recusar') }}</x-ui.button>
                            </form>
                        </span>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    <x-ui.card>
        <x-ui.card-title icon="group" class="mb-sm">{{ __('Seus amigos') }}</x-ui.card-title>

        @forelse ($friendships as $friendship)
            @php($friend = $friendship->otherParty(auth()->user()))

            @if ($loop->first)
                <ul class="flex flex-col gap-sm">
            @endif

            <li class="flex items-center justify-between gap-sm rounded-md border border-surface-variant bg-surface-bright px-md py-sm">
                <span class="flex min-w-0 items-center gap-sm">
                    <x-friends.avatar :user="$friend" class="size-9" />
                    <span class="truncate font-medium text-on-surface">{{ $friend->name }}</span>
                </span>

                <span class="flex shrink-0 items-center gap-sm">
                    <x-streak.flame :count="$friend->streak?->activeCount() ?? 0" />

                    <form method="POST" action="{{ route('friends.destroy', $friendship->id) }}"
                        onsubmit="return confirm('{{ __('Desfazer a amizade com :name?', ['name' => $friend->name]) }}')">
                        @csrf
                        @method('DELETE')
                        <button class="text-on-surface-variant transition duration-fast hover:text-error" title="{{ __('Desfazer amizade') }}">
                            <x-ui.icon name="person_remove" size="text-xl" />
                        </button>
                    </form>
                </span>
            </li>

            @if ($loop->last)
                </ul>
            @endif
        @empty
            <x-ui.empty-state icon="group_add" :title="__('Nenhum amigo ainda')"
                :description="__('Envie um pedido pelo e-mail de um colega para acompanhar o foguinho dele.')" />
        @endforelse
    </x-ui.card>

    @if ($sent->isNotEmpty())
        <x-ui.card>
            <x-ui.card-title icon="schedule_send" class="mb-sm">{{ __('Pedidos enviados') }}</x-ui.card-title>

            <ul class="flex flex-col gap-sm">
                @foreach ($sent as $request)
                    <li class="flex items-center justify-between gap-sm rounded-md border border-surface-variant bg-surface-bright px-md py-sm">
                        <span class="flex min-w-0 items-center gap-sm">
                            <x-friends.avatar :user="$request->addressee" class="size-9" />
                            <span class="truncate font-medium text-on-surface">{{ $request->addressee->name }}</span>
                        </span>

                        <form method="POST" action="{{ route('friends.destroy', $request->id) }}">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="secondary" class="!px-4 !py-1.5">{{ __('Cancelar') }}</x-ui.button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif
</x-app-layout>
