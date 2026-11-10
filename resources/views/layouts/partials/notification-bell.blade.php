{{-- Sino: últimas notificações + ativação do push neste navegador. Dados do NotificationBellComposer. --}}
<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" @click="open = ! open" :aria-expanded="open"
        aria-label="{{ trans_choice('{0} Notificações|{1} Notificações, :count não lida|[2,*] Notificações, :count não lidas', $unreadNotificationCount) }}"
        title="{{ __('Notificações') }}"
        class="relative flex size-[42px] items-center justify-center rounded-full bg-surface-container-low text-primary
               transition duration-fast hover:bg-surface-container active:scale-90">
        <x-ui.icon name="notifications" :filled="$unreadNotificationCount > 0" />

        @if ($unreadNotificationCount > 0)
            <span class="absolute -end-0.5 -top-0.5 flex min-w-5 items-center justify-center rounded-full bg-error px-1 font-mono text-[0.65rem] font-bold leading-5 text-on-error">
                {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-x-gutter top-[calc(72px+env(safe-area-inset-top,0px))] z-50 origin-top-right overflow-hidden rounded-md border border-glass
               bg-surface-container-lowest shadow-lg md:absolute md:inset-x-auto md:end-0 md:top-auto md:mt-2 md:w-96">

        <div class="flex items-center justify-between gap-sm border-b border-surface-variant px-4 py-3">
            <span class="font-bold text-on-surface">{{ __('Notificações') }}</span>

            @if ($unreadNotificationCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-primary hover:underline">
                        {{ __('Marcar todas como lidas') }}
                    </button>
                </form>
            @endif
        </div>

        {{-- Push neste navegador (resources/js/pwa.js). Some quando não há chave VAPID. --}}
        @if ($pushConfig['publicKey'])
            <div x-data="pushNotifications(@js($pushConfig))" x-cloak x-show="supported || needsInstall"
                class="border-b border-surface-variant bg-surface-container-low px-4 py-3 text-sm">
                <template x-if="needsInstall">
                    <p class="text-on-surface-variant">
                        {{ __('No iPhone, instale o app (Compartilhar → Adicionar à Tela de Início) para receber notificações.') }}
                    </p>
                </template>

                <template x-if="supported && permission === 'denied'">
                    <p class="text-on-surface-variant">
                        {{ __('As notificações estão bloqueadas neste navegador. Libere nas configurações do site para recebê-las.') }}
                    </p>
                </template>

                <template x-if="supported && permission !== 'denied' && ! subscribed">
                    <div class="flex items-center justify-between gap-sm">
                        <p class="text-on-surface-variant">{{ __('Receba avisos de mensagens, amizades, prazos e do foguinho.') }}</p>
                        <x-ui.button type="button" class="shrink-0 !px-4 !py-1.5 !text-xs" x-on:click="enable()" x-bind:disabled="busy">
                            {{ __('Ativar') }}
                        </x-ui.button>
                    </div>
                </template>

                <template x-if="supported && subscribed">
                    <div class="flex items-center justify-between gap-sm">
                        <p class="flex items-center gap-1.5 text-on-surface-variant">
                            <x-ui.icon name="notifications_active" size="text-lg" class="text-primary" />
                            {{ __('Notificações ativas neste dispositivo.') }}
                        </p>
                        <button type="button" class="shrink-0 text-xs font-semibold text-primary hover:underline"
                            @click="disable()" :disabled="busy">{{ __('Desativar') }}</button>
                    </div>
                </template>

                <p x-show="error" x-text="error" class="mt-1 text-xs text-error"></p>
            </div>
        @endif

        <div class="max-h-[min(60vh,420px)] overflow-y-auto">
            @forelse ($recentNotifications as $notification)
                <a href="{{ route('notifications.show', $notification->id) }}" @class([
                    'flex gap-3 border-b border-surface-variant px-4 py-3 last:border-b-0 transition duration-fast hover:bg-surface-container',
                    'bg-surface-container-low' => $notification->unread(),
                ])>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-surface-container text-primary">
                        <x-ui.icon :name="$notification->data['symbol'] ?? 'notifications'" size="text-xl" />
                    </span>

                    <span class="flex min-w-0 flex-1 flex-col">
                        <span class="flex items-start justify-between gap-2">
                            <span class="text-sm font-semibold text-on-surface">{{ $notification->data['title'] ?? '' }}</span>
                            @if ($notification->unread())
                                <span class="mt-1.5 size-2 shrink-0 rounded-full bg-primary" aria-label="{{ __('Não lida') }}"></span>
                            @endif
                        </span>
                        <span class="line-clamp-2 text-xs text-on-surface-variant">{{ $notification->data['body'] ?? '' }}</span>
                        <span class="mt-0.5 text-[0.7rem] text-outline">{{ $notification->created_at->diffForHumans() }}</span>
                    </span>
                </a>
            @empty
                <x-ui.empty-state icon="notifications_off" :title="__('Nenhuma notificação por aqui')"
                    :description="__('Avisos de amizades, prazos e do foguinho aparecem neste lugar.')" />
            @endforelse
        </div>
    </div>
</div>
