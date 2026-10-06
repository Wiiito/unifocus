@props(['subject', 'size' => 'size-2.5'])

<span {{ $attributes->class(['inline-block shrink-0 rounded-full', $size]) }}
    style="background-color: {{ $subject->displayColor() }}" aria-hidden="true"></span>
