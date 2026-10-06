@props(['id', 'confirm'])

{{-- Ações padrão de uma linha: editar e excluir (com confirmação). --}}
<div class="flex justify-end gap-sm">
    {{ $slot }}

    <button type="button" wire:click="openEditForm({{ $id }})" class="font-semibold text-primary hover:underline">
        {{ __('Editar') }}
    </button>

    <button type="button" wire:click="delete({{ $id }})" wire:confirm="{{ $confirm }}"
        class="font-semibold text-error hover:underline">
        {{ __('Excluir') }}
    </button>
</div>
