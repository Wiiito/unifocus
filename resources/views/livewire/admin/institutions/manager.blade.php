<div class="flex flex-col gap-md">
    <x-ui.page-header :title="__('Instituições')" :description="__('Instituições e as regras de aprovação herdadas pelas matrículas dos estudantes.')">
        <x-slot:actions>
            <x-ui.button type="button" wire:click="openCreateForm">
                <x-ui.icon name="add" size="text-xl" />
                {{ __('Nova instituição') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-admin.search-input :placeholder="__('Buscar por nome')" />

    <x-ui.flash :message="$flashMessage" />

    <x-admin.table :paginator="$this->institutions">
        <x-slot:head>
            <th class="px-4 py-3">{{ __('Instituição') }}</th>
            <th class="px-4 py-3">{{ __('Aprovação') }}</th>
            <th class="px-4 py-3">{{ __('Faltas') }}</th>
            <th class="px-4 py-3">{{ __('Estudantes') }}</th>
            <th class="px-4 py-3">{{ __('Matérias') }}</th>
            <th class="px-4 py-3">{{ __('Períodos') }}</th>
            <th class="px-4 py-3 text-right">{{ __('Ações') }}</th>
        </x-slot:head>

        @forelse ($this->institutions as $institution)
            <tr wire:key="institution-{{ $institution->id }}" class="border-b border-card-border last:border-0">
                <td class="px-4 py-3">
                    <span class="block font-semibold text-on-surface">{{ $institution->name }}</span>
                    <span class="font-mono text-xs text-on-surface-variant">{{ $institution->slug }}</span>
                </td>
                <td class="px-4 py-3 font-mono text-on-surface-variant">
                    {{ \Illuminate\Support\Number::format($institution->passing_percent, maxPrecision: 2) }}% {{ __('de') }} {{ $institution->total_points }}
                </td>
                <td class="px-4 py-3 font-mono text-on-surface-variant">{{ $institution->max_absence_percent }}%</td>
                <td class="px-4 py-3 font-mono text-on-surface-variant">{{ $institution->members_count }}</td>
                <td class="px-4 py-3 font-mono text-on-surface-variant">{{ $institution->subjects_count }}</td>
                <td class="px-4 py-3 font-mono text-on-surface-variant">{{ $institution->academic_terms_count }}</td>
                <td class="px-4 py-3">
                    <x-admin.row-actions :id="$institution->id"
                        :confirm="__('Remover :name? As matérias e os períodos dela deixam de aparecer para os estudantes.', ['name' => $institution->name])" />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-10 text-center text-on-surface-variant">{{ __('Nenhuma instituição cadastrada.') }}</td>
            </tr>
        @endforelse
    </x-admin.table>

    @if ($showForm)
        <x-admin.form-modal :title="$form->isEditing() ? __('Editar instituição') : __('Nova instituição')">
            <x-ui.field :label="__('Nome')" for="name" error="form.name">
                <x-text-input id="name" wire:model="form.name" class="block w-full" />
            </x-ui.field>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.field :label="__('Identificador (slug)')" for="slug" error="form.slug" :hint="__('Vazio = gerado pelo nome.')">
                    <x-text-input id="slug" wire:model="form.slug" class="block w-full" />
                </x-ui.field>

                <x-ui.field :label="__('CNPJ')" for="document" error="form.document">
                    <x-text-input id="document" wire:model="form.document" class="block w-full" />
                </x-ui.field>
            </div>

            <fieldset class="grid grid-cols-3 gap-4 rounded-md border border-surface-variant p-sm">
                <legend class="px-1 text-sm font-semibold text-on-surface-variant">{{ __('Regras de aprovação') }}</legend>

                <x-ui.field :label="__('Pontos no período')" for="totalPoints" error="form.totalPoints">
                    <x-text-input id="totalPoints" type="number" min="1" wire:model="form.totalPoints" class="block w-full" />
                </x-ui.field>

                <x-ui.field :label="__('% para aprovar')" for="passingPercent" error="form.passingPercent">
                    <x-text-input id="passingPercent" type="number" step="0.01" min="0" max="100" wire:model="form.passingPercent" class="block w-full" />
                </x-ui.field>

                <x-ui.field :label="__('% máx. de faltas')" for="maxAbsencePercent" error="form.maxAbsencePercent">
                    <x-text-input id="maxAbsencePercent" type="number" min="0" max="100" wire:model="form.maxAbsencePercent" class="block w-full" />
                </x-ui.field>
            </fieldset>
        </x-admin.form-modal>
    @endif
</div>
