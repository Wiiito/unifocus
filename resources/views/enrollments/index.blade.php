<x-app-layout>
    <x-ui.page-header :title="__('Minhas matérias')" :description="__('Notas, faltas e situação de cada matéria que você cursa.')">
        <x-slot:actions>
            <x-ui.button :href="route('enrollments.create')">
                <x-ui.icon name="add" size="text-xl" />
                {{ __('Adicionar matéria') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($ongoing->isEmpty() && $history->isEmpty())
        <x-ui.card>
            <x-ui.empty-state icon="menu_book" :title="__('Você ainda não adicionou matérias')"
                :description="__('Adicione as matérias do semestre para acompanhar pontos, faltas e prazos em um só lugar.')">
                <x-ui.button :href="route('enrollments.create')">{{ __('Adicionar a primeira matéria') }}</x-ui.button>
            </x-ui.empty-state>
        </x-ui.card>
    @endif

    @if ($ongoing->isNotEmpty())
        <section class="grid grid-cols-1 gap-md lg:grid-cols-2" aria-label="{{ __('Em andamento') }}">
            @foreach ($ongoing as $enrollment)
                <x-academic.enrollment-card :enrollment="$enrollment" />
            @endforeach
        </section>
    @endif

    @if ($history->isNotEmpty())
        <section class="flex flex-col gap-sm" aria-labelledby="history-title">
            <h2 id="history-title" class="text-lg font-bold text-on-surface">{{ __('Histórico') }}</h2>

            <div class="grid grid-cols-1 gap-md lg:grid-cols-2">
                @foreach ($history as $enrollment)
                    <x-academic.enrollment-card :enrollment="$enrollment" class="opacity-90" />
                @endforeach
            </div>
        </section>
    @endif
</x-app-layout>
