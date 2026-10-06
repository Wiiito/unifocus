{{-- Peso da nota no total do período; em branco vale 1. --}}
<x-ui.field :label="__('Peso')" for="weight" :hint="__('Em branco = 1. Recuperação costuma ter peso maior.')">
    <x-text-input id="weight" name="weight" type="number" step="0.01" min="0.01" class="block w-full"
        :value="old('weight', $weight != 1 ? $weight : null)" placeholder="1" />
</x-ui.field>
