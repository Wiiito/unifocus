@props([
    'disabled' => false,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
])

{{-- Select no mesmo estilo do text-input. Aceita options (valor => rótulo) ou <option>s no slot. --}}
<select @disabled($disabled) {{ $attributes->class([
    'rounded-md border-outline-variant bg-surface-container-lowest text-on-surface shadow-sm',
    'transition duration-fast focus:border-primary focus:ring-primary disabled:opacity-60',
]) }}>
    @if ($placeholder !== null)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach ($options as $value => $label)
        <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $label }}</option>
    @endforeach

    {{ $slot }}
</select>
