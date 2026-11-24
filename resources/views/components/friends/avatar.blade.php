@props(['user'])

{{-- Foto do Google quando o estudante entrou com ele; senão, avatar de iniciais. --}}
@if ($user->avatar_url)
<img src="{{ $user->avatar_url }}" alt="" title="{{ $user->name }}" referrerpolicy="no-referrer" loading="lazy"
    {{ $attributes->class(['inline-block shrink-0 rounded-full object-cover']) }} />
@else
<span {{ $attributes->class([
    'inline-flex shrink-0 items-center justify-center rounded-full bg-tertiary-container text-xs font-bold uppercase text-white',
]) }} title="{{ $user->name }}" aria-hidden="true">
    {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->filter()->take(2)->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->implode('') }}
</span>
@endif
