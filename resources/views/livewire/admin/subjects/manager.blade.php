<div class="flex flex-col gap-md">
    <div class="flex items-center justify-between gap-sm">
        <div>
            <h1 class="text-2xl font-bold text-primary">{{ __('Matérias') }}</h1>
            <p class="mt-1 text-sm text-on-surface-variant">
                {{ __('Cadastre, atualize e remova as matérias do catálogo.') }}
            </p>
        </div>

        <x-ui.button type="button" wire:click="openCreateForm">
            <x-ui.icon name="add" size="text-xl" />
            {{ __('Nova matéria') }}
        </x-ui.button>
    </div>

    @if ($flashMessage)
        <div class="rounded-md border border-emerald-500/30 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-600">
            {{ $flashMessage }}
        </div>
    @endif

    <x-ui.card :inner="false">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-card-border text-xs font-bold uppercase tracking-wide text-on-surface-variant">
                    <tr>
                        <th class="px-4 py-3">{{ __('Matéria') }}</th>
                        <th class="px-4 py-3">{{ __('Código') }}</th>
                        <th class="px-4 py-3">{{ __('Créditos') }}</th>
                        <th class="px-4 py-3">{{ __('Carga horária') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->subjects as $subject)
                        <tr wire:key="subject-{{ $subject->id }}" class="border-b border-card-border last:border-0">
                            <td class="px-4 py-3">
                                <span class="mr-2 inline-block size-2.5 rounded-full align-middle" style="background-color: {{ $subject->color ?? '#5c7cfa' }}"></span>
                                {{ $subject->name }}
                            </td>
                            <td class="px-4 py-3 text-on-surface-variant">{{ $subject->code ?? '—' }}</td>
                            <td class="px-4 py-3 text-on-surface-variant">{{ $subject->credits ?? '—' }}</td>
                            <td class="px-4 py-3 text-on-surface-variant">{{ $subject->workload_hours ? $subject->workload_hours.'h' : '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-sm">
                                    <button type="button" wire:click="openEditForm({{ $subject->id }})"
                                        class="font-semibold text-primary hover:underline">
                                        {{ __('Editar') }}
                                    </button>

                                    <button type="button" wire:click="deleteSubject({{ $subject->id }})"
                                        wire:confirm="{{ __('Tem certeza que deseja remover a matéria :name?', ['name' => $subject->name]) }}"
                                        class="font-semibold text-error hover:underline">
                                        {{ __('Excluir') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-on-surface-variant">
                                {{ __('Nenhuma matéria cadastrada ainda.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    {{ $this->subjects->links() }}

    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-gutter py-lg" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-surface-dim opacity-75" wire:click="closeForm"></div>

            <x-ui.card class="relative z-10 w-full max-w-lg overflow-y-auto" style="max-height: calc(100vh - 4rem);">
                <div class="flex items-center justify-between">
                    <x-ui.card-title>
                        {{ $editingSubjectId ? __('Editar matéria') : __('Nova matéria') }}
                    </x-ui.card-title>

                    <button type="button" wire:click="closeForm" aria-label="{{ __('Fechar') }}"
                        class="text-on-surface-variant hover:text-on-surface">
                        <x-ui.icon name="close" />
                    </button>
                </div>

                <form wire:submit="save" class="mt-4 flex flex-col gap-4">
                    <div>
                        <x-input-label for="name" :value="__('Nome')" />
                        <x-text-input id="name" wire:model="name" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="code" :value="__('Código')" />
                            <x-text-input id="code" wire:model="code" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('code')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="color" :value="__('Cor')" />
                            <input id="color" type="color" wire:model="color"
                                class="mt-1 block h-[42px] w-full rounded-md border-outline-variant bg-surface-container-lowest shadow-sm">
                            <x-input-error :messages="$errors->get('color')" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="credits" :value="__('Créditos')" />
                            <x-text-input id="credits" type="number" min="0" wire:model="credits" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('credits')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="workloadHours" :value="__('Carga horária (h)')" />
                            <x-text-input id="workloadHours" type="number" min="0" wire:model="workloadHours" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('workloadHours')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Descrição')" />
                        <textarea id="description" wire:model="description" rows="3"
                            class="mt-1 block w-full rounded-md border-outline-variant bg-surface-container-lowest text-on-surface shadow-sm transition duration-fast focus:border-primary focus:ring-primary"></textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>

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
    @endif
</div>
