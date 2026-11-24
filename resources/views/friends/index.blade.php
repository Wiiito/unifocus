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
                <a href="{{ route('messages.show', $friend->id) }}" title="{{ __('Conversar com :name', ['name' => $friend->name]) }}"
                    class="group flex min-w-0 flex-1 items-center gap-sm">
                    <x-friends.avatar :user="$friend" class="size-9" />
                    <span class="truncate font-medium text-on-surface transition duration-fast group-hover:text-primary">{{ $friend->name }}</span>
                    <x-ui.icon name="chat" size="text-lg" class="shrink-0 text-on-surface-variant transition duration-fast group-hover:text-primary" />
                </a>

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

    <x-ui.card>
        <div class="mb-sm flex items-center justify-between gap-sm">
            <x-ui.card-title icon="near_me">{{ __('Pessoas próximas') }}</x-ui.card-title>

            <button type="button" x-data="shareLocationButton" @click="share" :disabled="busy"
                class="flex shrink-0 items-center gap-1 text-sm font-medium text-primary transition duration-fast hover:text-primary-hover disabled:opacity-50">
                <x-ui.icon name="my_location" size="text-lg" />
                <span x-text="busy ? '{{ __('Localizando...') }}' : '{{ __('Atualizar') }}'">{{ __('Atualizar') }}</span>
            </button>
        </div>

        <p class="mb-sm text-xs text-on-surface-variant">
            {{ __('Quem usou o UniFocus a até :km km de você nos últimos :days dias. A distância é aproximada e ninguém vê a sua localização exata.', ['km' => \App\Support\Locations\NearbyStudents::RADIUS_KM, 'days' => \App\Support\Locations\NearbyStudents::RECENT_DAYS]) }}
        </p>

        @if ($nearby === null)
            <x-ui.empty-state icon="location_off" :title="__('Localização desativada')"
                :description="__('Permita a localização no navegador e toque em Atualizar para ver quem estuda perto de você.')" />
        @elseif ($nearby->isEmpty())
            <x-ui.empty-state icon="person_search" :title="__('Ninguém por perto agora')"
                :description="__('Quando outros estudantes usarem o UniFocus perto de você, eles aparecem aqui.')" />
        @else
            <ul class="flex flex-col gap-sm">
                @foreach ($nearby as $person)
                    <li class="flex items-center justify-between gap-sm rounded-md border border-surface-variant bg-surface-bright px-md py-sm">
                        <span class="flex min-w-0 items-center gap-sm">
                            <x-friends.avatar :user="$person['user']" class="size-9" />
                            <span class="flex min-w-0 flex-col">
                                <span class="truncate font-medium text-on-surface">{{ $person['user']->name }}</span>
                                <span class="text-xs text-on-surface-variant">
                                    {{ $person['distance_km'] < 1 ? __('a menos de 1 km') : __('a cerca de :km km', ['km' => round($person['distance_km'])]) }}
                                </span>
                            </span>
                        </span>

                        @if (in_array($person['user']->id, $friendIds, true))
                            <a href="{{ route('messages.show', $person['user']->id) }}" class="flex shrink-0 items-center gap-1 text-sm font-medium text-primary hover:text-primary-hover">
                                <x-ui.icon name="chat" size="text-lg" />
                                {{ __('Mensagem') }}
                            </a>
                        @elseif (in_array($person['user']->id, $pendingIds, true))
                            <span class="shrink-0 text-xs font-medium text-on-surface-variant">{{ __('Pedido pendente') }}</span>
                        @else
                            <form method="POST" action="{{ route('friends.store') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $person['user']->id }}">
                                <x-ui.button class="!px-4 !py-1.5">{{ __('Adicionar') }}</x-ui.button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
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
