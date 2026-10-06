<x-app-layout>
    <x-ui.page-header :title="__('Boletim')" :description="__('Pontos, faltas e resultado de todas as matérias, por período.')" />

    @forelse ($terms as $termName => $enrollments)
        <x-ui.card :inner="false">
            <h2 class="border-b border-card-border px-md py-sm text-lg font-bold text-primary">{{ $termName }}</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-card-border text-xs font-bold uppercase tracking-wide text-on-surface-variant">
                        <tr>
                            <th class="px-4 py-3">{{ __('Matéria') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Pontos') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Mínimo') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Faltas') }}</th>
                            <th class="px-4 py-3">{{ __('Situação') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($enrollments as $enrollment)
                            @php
                                $rules = $enrollment->academicRules();
                                $allowedAbsences = $enrollment->allowedAbsences();
                            @endphp

                            <tr class="border-b border-card-border last:border-0">
                                <td class="px-4 py-3">
                                    <a href="{{ route('enrollments.show', $enrollment) }}" class="flex items-center gap-2 font-semibold text-on-surface hover:text-primary">
                                        <x-ui.subject-dot :subject="$enrollment->subject()" />
                                        {{ $enrollment->subject()->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-right font-mono">
                                    {{ $enrollment->final_grade === null ? '—' : \Illuminate\Support\Number::format($enrollment->final_grade, maxPrecision: 2) }}{{ $rules ? '/'.$rules->totalPoints : '' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-on-surface-variant">
                                    {{ $rules ? \Illuminate\Support\Number::format($rules->passingPoints(), maxPrecision: 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono">
                                    {{ $enrollment->absence_count }}{{ $allowedAbsences !== null ? '/'.$allowedAbsences : '' }}
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.badge :variant="$enrollment->final_status->badgeVariant()">{{ $enrollment->final_status->label() }}</x-ui.badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @empty
        <x-ui.card>
            <x-ui.empty-state icon="description" :title="__('Boletim vazio')"
                :description="__('As matérias que você adicionar aparecem aqui com pontos, faltas e resultado.')">
                <x-ui.button :href="route('enrollments.create')">{{ __('Adicionar matéria') }}</x-ui.button>
            </x-ui.empty-state>
        </x-ui.card>
    @endforelse
</x-app-layout>
