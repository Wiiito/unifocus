<div class="flex flex-col gap-md">
    <x-ui.page-header :title="__('Períodos letivos')" :description="__('Calendário de cada instituição: datas do período e marcos como semana de provas e recesso.')">
        <x-slot:actions>
            <x-ui.button type="button" wire:click="openCreateForm" :disabled="$this->institutions->isEmpty()">
                <x-ui.icon name="add" size="text-xl" />
                {{ __('Novo período') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-select-input wire:model.live="institution" class="sm:w-72" :aria-label="__('Filtrar por instituição')"
        :placeholder="__('Todas as instituições')" :options="$this->institutions" />

    @if ($this->institutions->isEmpty())
        <p class="text-sm text-on-surface-variant">
            {{ __('Cadastre uma instituição antes: todo período pertence a uma.') }}
            <a href="{{ route('admin.institutions.index') }}" class="font-semibold text-primary hover:underline">{{ __('Ir para instituições') }}</a>
        </p>
    @endif

    <x-ui.flash :message="$flashMessage" />

    <x-admin.table :paginator="$this->terms">
        <x-slot:head>
            <th class="px-4 py-3">{{ __('Período') }}</th>
            <th class="px-4 py-3">{{ __('Instituição') }}</th>
            <th class="px-4 py-3">{{ __('Datas') }}</th>
            <th class="px-4 py-3">{{ __('Marcos') }}</th>
            <th class="px-4 py-3">{{ __('Turmas') }}</th>
            <th class="px-4 py-3 text-right">{{ __('Ações') }}</th>
        </x-slot:head>

        @forelse ($this->terms as $term)
            <tr wire:key="term-{{ $term->id }}" class="border-b border-card-border last:border-0">
                <td class="px-4 py-3">
                    <span class="flex items-center gap-2 font-semibold text-on-surface">
                        {{ $term->name }}
                        @if ($term->isCurrent())
                            <x-ui.badge variant="success">{{ __('Atual') }}</x-ui.badge>
                        @endif
                    </span>
                </td>
                <td class="px-4 py-3 text-on-surface-variant">{{ $term->institution->name }}</td>
                <td class="px-4 py-3 font-mono text-on-surface-variant">{{ $term->starts_on->format('d/m/Y') }} – {{ $term->ends_on->format('d/m/Y') }}</td>
                <td class="px-4 py-3 font-mono text-on-surface-variant">{{ $term->events_count }}</td>
                <td class="px-4 py-3 font-mono text-on-surface-variant">{{ $term->class_groups_count }}</td>
                <td class="px-4 py-3">
                    <x-admin.row-actions :id="$term->id"
                        :confirm="__('Remover o período :name? As turmas dos estudantes ficam sem período.', ['name' => $term->name])" />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-10 text-center text-on-surface-variant">{{ __('Nenhum período cadastrado.') }}</td>
            </tr>
        @endforelse
    </x-admin.table>

    @if ($showForm)
        <x-admin.form-modal :title="$form->isEditing() ? __('Editar período') : __('Novo período')" max-width="max-w-2xl">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.field :label="__('Instituição')" for="institutionId" error="form.institutionId">
                    <x-select-input id="institutionId" wire:model="form.institutionId" class="block w-full"
                        :placeholder="__('Selecione...')" :options="$this->institutions" />
                </x-ui.field>

                <x-ui.field :label="__('Nome')" for="name" error="form.name">
                    <x-text-input id="name" wire:model="form.name" class="block w-full" :placeholder="__('Ex.: 2026.2')" />
                </x-ui.field>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.field :label="__('Início')" for="startsOn" error="form.startsOn">
                    <x-text-input id="startsOn" type="date" wire:model="form.startsOn" class="block w-full" />
                </x-ui.field>

                <x-ui.field :label="__('Fim')" for="endsOn" error="form.endsOn">
                    <x-text-input id="endsOn" type="date" wire:model="form.endsOn" class="block w-full" />
                </x-ui.field>
            </div>

            <fieldset class="flex flex-col gap-2">
                <legend class="text-sm font-semibold text-on-surface-variant">{{ __('Marcos do calendário') }}</legend>

                @foreach ($form->events as $index => $event)
                    <div wire:key="event-{{ $index }}" class="grid grid-cols-1 gap-2 rounded-md border border-surface-variant p-sm sm:grid-cols-[2fr_1fr_1fr_1fr_auto] sm:items-start">
                        <div>
                            <x-text-input wire:model="form.events.{{ $index }}.title" class="block w-full" :placeholder="__('Título')" :aria-label="__('Título do marco')" />
                            <x-input-error :messages="$errors->get('form.events.'.$index.'.title')" class="mt-1" />
                        </div>
                        <x-select-input wire:model="form.events.{{ $index }}.type" class="block w-full" :aria-label="__('Tipo')"
                            :options="\App\Enums\TermEventType::options()" />
                        <div>
                            <x-text-input type="date" wire:model="form.events.{{ $index }}.starts_on" class="block w-full" :aria-label="__('Início do marco')" />
                            <x-input-error :messages="$errors->get('form.events.'.$index.'.starts_on')" class="mt-1" />
                        </div>
                        <div>
                            <x-text-input type="date" wire:model="form.events.{{ $index }}.ends_on" class="block w-full" :aria-label="__('Fim do marco')" />
                            <x-input-error :messages="$errors->get('form.events.'.$index.'.ends_on')" class="mt-1" />
                        </div>
                        <button type="button" wire:click="removeEvent({{ $index }})" class="self-center text-on-surface-variant hover:text-error"
                            aria-label="{{ __('Remover marco') }}">
                            <x-ui.icon name="delete" size="text-xl" />
                        </button>
                    </div>
                @endforeach

                <button type="button" wire:click="addEvent" class="inline-flex w-fit items-center gap-1 text-sm font-semibold text-primary hover:underline">
                    <x-ui.icon name="add" size="text-lg" />
                    {{ __('Adicionar marco (semana de provas, recesso...)') }}
                </button>
            </fieldset>
        </x-admin.form-modal>
    @endif
</div>
