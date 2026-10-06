@props(['board'])

{{--
    Desafios diários. Não há checkbox manual: cada desafio se cumpre sozinho
    a partir do que já está no banco (presença em aula, visita à agenda e
    respostas a questões). Cumprir os três mantém o foguinho aceso.
--}}
<x-ui.card>
    <div class="mb-md flex items-center justify-between gap-sm border-b border-surface-variant pb-sm">
        <x-ui.card-title>{{ __('Desafios Diários') }}</x-ui.card-title>

        <x-ui.badge :variant="$board->isComplete() ? 'success' : 'tertiary'">
            {{ $board->completedCount() }}/{{ $board->total() }} {{ __('Completos') }}
        </x-ui.badge>
    </div>

    <div class="grid grid-cols-1 gap-sm md:grid-cols-3">
        @foreach ($board->challenges as $status)
            <a href="{{ $status->challenge->url() }}" @class([
                'flex items-center gap-sm rounded-md border px-md py-sm transition duration-fast active:scale-[0.97]',
                'border-primary bg-primary-container/10' => $status->isDone(),
                'border-surface-variant bg-surface-bright hover:bg-surface-container-low' => ! $status->isDone(),
            ])>
                <span @class([
                    'flex size-[26px] shrink-0 items-center justify-center rounded-[6px] border-2',
                    'border-primary bg-primary text-on-primary' => $status->isDone(),
                    'border-outline text-outline' => ! $status->isDone(),
                ]) aria-hidden="true">
                    <x-ui.icon :name="$status->isDone() ? 'check' : $status->challenge->icon()" size="text-lg" />
                </span>

                <span class="flex min-w-0 flex-1 flex-col">
                    <span @class([
                        'text-[0.95rem] font-semibold',
                        'text-on-surface-variant line-through' => $status->isDone(),
                        'text-on-surface' => ! $status->isDone(),
                    ])>{{ $status->challenge->label() }}</span>
                    <span class="truncate text-xs text-on-surface-variant">{{ $status->challenge->description() }}</span>
                </span>

                <span class="shrink-0 font-mono text-xs font-bold text-on-surface-variant">
                    {{ $status->displayProgress() }}/{{ $status->target() }}
                </span>
            </a>
        @endforeach
    </div>
</x-ui.card>
