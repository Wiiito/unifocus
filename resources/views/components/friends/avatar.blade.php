@props(['user'])

{{-- Avatar de iniciais: a plataforma ainda não tem foto de perfil. --}}
<span {{ $attributes->class([
    'inline-flex shrink-0 items-center justify-center rounded-full bg-tertiary-container text-xs font-bold uppercase text-white',
]) }} title="{{ $user->name }}" aria-hidden="true">
    {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->filter()->take(2)->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->implode('') }}
</span>
