@props(['title', 'maxWidth' => 'max-w-lg'])

{{--
    Modal de formulário dos cadastros do painel. Usado dentro de componentes
    que aplicam o trait ManagesResourceForm (ações save/closeForm).
--}}
<div class="fixed inset-0 z-50 flex items-center justify-center px-gutter py-lg" role="dialog" aria-modal="true"
    x-data x-on:keydown.escape.window="$wire.closeForm()">
    <div class="fixed inset-0 bg-surface-dim opacity-75" wire:click="closeForm"></div>

    <x-ui.card class="relative z-10 w-full {{ $maxWidth }} overflow-y-auto" style="max-height: calc(100vh - 4rem);">
        <div class="flex items-center justify-between">
            <x-ui.card-title>{{ $title }}</x-ui.card-title>

            <button type="button" wire:click="closeForm" aria-label="{{ __('Fechar') }}"
                class="text-on-surface-variant hover:text-on-surface">
                <x-ui.icon name="close" />
            </button>
        </div>

        <form wire:submit="save" class="mt-4 flex flex-col gap-4">
            {{ $slot }}

            <div class="flex justify-end gap-2">
                <x-ui.button type="button" variant="secondary" wire:click="closeForm">
                    {{ __('Cancelar') }}
                </x-ui.button>

                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="save">
                    {{ __('Salvar') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
