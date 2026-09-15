@php
    /** Notas mockadas no front até existir a integração com a faculdade. */
    $subjects = [
        ['name' => 'Cálculo I', 'grade' => '8.5', 'dot' => 'bg-primary-container', 'variant' => 'primary'],
        ['name' => 'Algoritmos', 'grade' => '9.0', 'dot' => 'bg-secondary', 'variant' => 'primary'],
        ['name' => 'Física Geral', 'grade' => '7.2', 'dot' => 'bg-outline', 'variant' => 'neutral'],
    ];
@endphp

<x-ui.card>
    <div class="mb-md flex items-center justify-between">
        <x-ui.card-title>{{ __('Resumo de Notas') }}</x-ui.card-title>
        <x-ui.icon name="analytics" size="text-[28px]" class="text-tertiary" />
    </div>

    <ul class="flex flex-1 flex-col gap-sm" aria-label="{{ __('Notas por disciplina') }}">
        @foreach ($subjects as $subject)
            <li tabindex="0"
                class="flex cursor-pointer items-center justify-between rounded-md border border-surface-variant bg-surface-bright px-3.5 py-2.5
                       transition duration-fast hover:bg-surface-container-low active:scale-[0.98]
                       focus:outline-none focus:ring-2 focus:ring-primary">
                <div class="flex items-center gap-2.5">
                    <span class="size-2 rounded-full {{ $subject['dot'] }}"></span>
                    <span class="text-[0.95rem] font-semibold text-on-surface">{{ $subject['name'] }}</span>
                </div>

                <x-ui.badge :variant="$subject['variant']">{{ $subject['grade'] }}</x-ui.badge>
            </li>
        @endforeach
    </ul>
</x-ui.card>
