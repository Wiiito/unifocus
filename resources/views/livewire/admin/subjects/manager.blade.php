<div class="flex flex-col gap-md">
    <x-ui.page-header :title="__('Matérias')" :description="__('Catálogo de matérias. Sem instituição, a matéria fica disponível para todos os estudantes.')">
        <x-slot:actions>
            <x-ui.button type="button" wire:click="openCreateForm">
                <x-ui.icon name="add" size="text-xl" />
                {{ __('Nova matéria') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="flex flex-col gap-sm sm:flex-row">
        <x-admin.search-input :placeholder="__('Buscar por nome ou código')" />

        <x-select-input wire:model.live="institution" :aria-label="__('Filtrar por instituição')" :placeholder="__('Todas as instituições')">
            <option value="general">{{ __('Catálogo geral') }}</option>
            @foreach ($this->institutions as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </x-select-input>
    </div>

    <x-ui.flash :message="$flashMessage" />

    <x-admin.table :paginator="$this->subjects">
        <x-slot:head>
            <th class="px-4 py-3">{{ __('Matéria') }}</th>
            <th class="px-4 py-3">{{ __('Instituição') }}</th>
            <th class="px-4 py-3">{{ __('Código') }}</th>
            <th class="px-4 py-3">{{ __('Carga horária') }}</th>
            <th class="px-4 py-3">{{ __('Turmas') }}</th>
            <th class="px-4 py-3 text-right">{{ __('Ações') }}</th>
        </x-slot:head>

        @forelse ($this->subjects as $subject)
            <tr wire:key="subject-{{ $subject->id }}" class="border-b border-card-border last:border-0">
                <td class="px-4 py-3">
                    <span class="flex items-center gap-2">
                        <x-ui.subject-dot :subject="$subject" />
                        {{ $subject->name }}
                    </span>
                </td>
                <td class="px-4 py-3 text-on-surface-variant">{{ $subject->institution?->name ?? __('Catálogo geral') }}</td>
                <td class="px-4 py-3 text-on-surface-variant">{{ $subject->code ?? '—' }}</td>
                <td class="px-4 py-3 text-on-surface-variant">{{ $subject->workload_hours ? $subject->workload_hours.'h' : '—' }}</td>
                <td class="px-4 py-3 font-mono text-on-surface-variant">{{ $subject->class_groups_count }}</td>
                <td class="px-4 py-3">
                    <x-admin.row-actions :id="$subject->id"
                        :confirm="__('Tem certeza que deseja remover a matéria :name?', ['name' => $subject->name])" />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-10 text-center text-on-surface-variant">
                    {{ __('Nenhuma matéria encontrada.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>

    @if ($showForm)
        <x-admin.form-modal :title="$form->isEditing() ? __('Editar matéria') : __('Nova matéria')">
            <x-ui.field :label="__('Nome')" for="name" error="form.name">
                <x-text-input id="name" wire:model="form.name" class="block w-full" />
            </x-ui.field>

            <x-ui.field :label="__('Instituição')" for="institutionId" error="form.institutionId">
                <x-select-input id="institutionId" wire:model="form.institutionId" class="block w-full"
                    :placeholder="__('Catálogo geral (todos os estudantes)')" :options="$this->institutions" />
            </x-ui.field>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.field :label="__('Código')" for="code" error="form.code">
                    <x-text-input id="code" wire:model="form.code" class="block w-full" />
                </x-ui.field>

                <x-ui.field :label="__('Cor')" for="color" error="form.color">
                    <input id="color" type="color" wire:model="form.color"
                        class="block h-[42px] w-full rounded-md border-outline-variant bg-surface-container-lowest shadow-sm">
                </x-ui.field>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.field :label="__('Créditos')" for="credits" error="form.credits">
                    <x-text-input id="credits" type="number" min="0" wire:model="form.credits" class="block w-full" />
                </x-ui.field>

                <x-ui.field :label="__('Carga horária (h)')" for="workloadHours" error="form.workloadHours">
                    <x-text-input id="workloadHours" type="number" min="0" wire:model="form.workloadHours" class="block w-full" />
                </x-ui.field>
            </div>

            <x-ui.field :label="__('Descrição')" for="description" error="form.description">
                <x-textarea-input id="description" wire:model="form.description" class="block w-full" />
            </x-ui.field>
        </x-admin.form-modal>
    @endif
</div>
