<x-app-layout>
    {{-- Linha 1: estatísticas rápidas --}}
    <section class="grid grid-cols-1 gap-md md:grid-cols-2" aria-label="{{ __('Estatísticas Rápidas') }}">
        <x-dashboard.streaks :board="$dailyBoard" />
        <x-dashboard.grades :enrollments="$enrollments" />
    </section>

    {{-- Linha 2: revisão diária e cronograma --}}
    <section class="grid grid-cols-1 gap-md lg:grid-cols-[2fr_1fr]" aria-label="{{ __('Estudos e Atividades') }}">
        <x-dashboard.flashcard :deck="$deck" />
        <x-dashboard.upcoming :items="$upcoming" />
    </section>

    {{-- Linha 3: desafios --}}
    <section id="desafios" class="scroll-mt-28" aria-label="{{ __('Desafios Diários') }}">
        <x-dashboard.challenges :board="$dailyBoard" />
    </section>
</x-app-layout>
