<section>
    <header>
        <h2 class="text-lg font-medium text-on-surface">{{ __('Onde você estuda') }}</h2>

        <p class="mt-1 text-sm text-on-surface-variant">
            {{ __('Vincule-se à sua instituição para ver as matérias e os períodos letivos dela. Opcional: sem instituição você usa o catálogo geral.') }}
        </p>
    </header>

    @if (in_array(session('status'), ['membership-created', 'membership-deleted'], true))
        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)"
            class="mt-4 text-sm text-on-surface-variant">
            {{ session('status') === 'membership-created' ? __('Instituição vinculada.') : __('Vínculo removido.') }}
        </p>
    @endif

    @if ($memberships->isNotEmpty())
        <ul class="mt-4 flex flex-col gap-2">
            @foreach ($memberships as $membership)
                <li class="flex items-center justify-between gap-sm rounded-md border border-surface-variant bg-surface-bright px-4 py-2.5">
                    <span class="flex flex-col">
                        <span class="font-semibold text-on-surface">{{ $membership->institution->name }}</span>
                        <span class="text-xs text-on-surface-variant">
                            {{ $membership->role->label() }}
                            @if ($membership->registration_code)
                                · {{ __('Matrícula') }} {{ $membership->registration_code }}
                            @endif
                        </span>
                    </span>

                    <form method="POST" action="{{ route('institution-memberships.destroy', $membership->id) }}"
                        onsubmit="return confirm(@js(__('Remover o vínculo com :name?', ['name' => $membership->institution->name])))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-error hover:underline">{{ __('Remover') }}</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($institutions->isNotEmpty())
        <form method="POST" action="{{ route('institution-memberships.store') }}" class="mt-6 grid grid-cols-1 items-end gap-4 sm:grid-cols-[2fr_1fr_auto]">
            @csrf

            <x-ui.field :label="__('Instituição')" for="institution_id">
                <x-select-input id="institution_id" name="institution_id" class="block w-full" required
                    :placeholder="__('Selecione...')" :options="$institutions->pluck('name', 'id')" :selected="old('institution_id')" />
            </x-ui.field>

            <x-ui.field :label="__('Nº de matrícula')" for="registration_code">
                <x-text-input id="registration_code" name="registration_code" class="block w-full" maxlength="40" :value="old('registration_code')" />
            </x-ui.field>

            <x-primary-button>{{ __('Vincular') }}</x-primary-button>
        </form>
    @endif
</section>
