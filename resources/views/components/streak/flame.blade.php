@props(['count'])

{{--
    Foguinho: componente único de exibição, para o próprio estudante
    (cabeçalho) e para outras pessoas (amigos, painel admin).
    Recebe só o número: quem chama lê de UserStreak::activeCount().
--}}
@php
    $isLit = $count > 0;
@endphp

<span {{ $attributes->class([
    'inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5',
    'border-streak-border bg-streak-bg' => $isLit,
    'border-outline-variant bg-surface-container-low' => ! $isLit,
]) }} title="{{ trans_choice('{0} Foguinho apagado|{1} :count dia de foguinho|[2,*] :count dias de foguinho', $count) }}">
    <span @class(['font-mono text-[0.95rem] font-bold', 'text-streak' => $isLit, 'text-outline' => ! $isLit])>{{ $count }}</span>
    <x-ui.icon name="local_fire_department" filled size="text-[22px]"
        :class="$isLit ? 'text-streak animate-flame-pulse' : 'text-outline'" />
</span>
