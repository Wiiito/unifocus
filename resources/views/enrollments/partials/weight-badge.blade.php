{{-- Só aparece quando o peso foge do padrão (1). --}}
@if ($weight != 1)
    <x-ui.badge variant="streak" title="{{ __('Peso') }}">×{{ \Illuminate\Support\Number::format($weight, maxPrecision: 2) }}</x-ui.badge>
@endif
