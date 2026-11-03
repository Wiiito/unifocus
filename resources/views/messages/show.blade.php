<x-app-layout>
    <x-ui.page-header :title="$friend->name" :back="route('messages.index')" />

    <x-ui.card>
        <div id="chat-log" class="flex h-[55vh] flex-col gap-2 overflow-y-auto pb-sm" aria-live="polite">
            @foreach ($messages as $message)
                <div data-id="{{ $message->id }}" @class([
                    'max-w-[80%] rounded-lg px-3 py-2 text-sm',
                    'self-end bg-primary text-on-primary' => $message->sender_id === auth()->id(),
                    'self-start bg-surface-container text-on-surface' => $message->sender_id !== auth()->id(),
                ])>
                    <p class="whitespace-pre-wrap break-words">{{ $message->body }}</p>
                    <span class="mt-0.5 block text-right text-[0.65rem] opacity-70">{{ $message->created_at->format('H:i') }}</span>
                </div>
            @endforeach
        </div>

        <form id="chat-form" method="POST" action="{{ route('messages.store', $friend->id) }}"
            class="mt-sm flex items-start gap-sm border-t border-surface-variant pt-sm">
            @csrf
            <x-text-input id="chat-body" name="body" class="block w-full" maxlength="2000" autocomplete="off"
                :placeholder="__('Escreva uma mensagem')" :aria-label="__('Mensagem')" required />
            <x-ui.button>{{ __('Enviar') }}</x-ui.button>
        </form>
        <x-input-error :messages="$errors->get('body')" class="mt-1" />
    </x-ui.card>

    {{-- Polling: pede só o que veio depois da última mensagem exibida; pausa com a aba oculta. --}}
    <script>
        (() => {
            const log = document.getElementById('chat-log');
            const form = document.getElementById('chat-form');
            const input = document.getElementById('chat-body');
            const pollUrl = @json(route('messages.poll', $friend->id));
            const csrf = @json(csrf_token());
            const intervalMs = 3000;
            let lastId = {{ $messages->last()?->id ?? 0 }};
            let busy = false;

            const append = (message) => {
                if (message.id <= lastId) return;
                lastId = message.id;

                const bubble = document.createElement('div');
                bubble.dataset.id = message.id;
                bubble.className = 'max-w-[80%] rounded-lg px-3 py-2 text-sm ' + (message.mine
                    ? 'self-end bg-primary text-on-primary'
                    : 'self-start bg-surface-container text-on-surface');

                const text = document.createElement('p');
                text.className = 'whitespace-pre-wrap break-words';
                text.textContent = message.body;

                const time = document.createElement('span');
                time.className = 'mt-0.5 block text-right text-[0.65rem] opacity-70';
                time.textContent = message.time;

                bubble.append(text, time);
                log.append(bubble);
                log.scrollTop = log.scrollHeight;
            };

            const poll = async () => {
                if (busy || document.hidden) return;
                busy = true;
                try {
                    const response = await fetch(`${pollUrl}?after=${lastId}`, { headers: { Accept: 'application/json' } });
                    if (response.ok) (await response.json()).messages.forEach(append);
                } catch (error) {
                    /* sem rede: tenta de novo no próximo ciclo */
                } finally {
                    busy = false;
                }
            };

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const body = input.value.trim();
                if (!body) return;
                input.value = '';

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ body }),
                });

                if (response.ok) {
                    append((await response.json()).message);
                } else {
                    input.value = body;
                }
            });

            log.scrollTop = log.scrollHeight;
            setInterval(poll, intervalMs);
            document.addEventListener('visibilitychange', poll);
        })();
    </script>
</x-app-layout>
