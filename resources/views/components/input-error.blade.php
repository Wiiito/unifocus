@props(['messages'])

@if ($messages)
    <ul {{ $attributes->class(['space-y-1 text-sm font-medium text-error']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
