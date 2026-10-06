@props(['message' => session('flash')])

{{-- Mensagem de sucesso após um redirect (session('flash')) ou de uma ação Livewire. --}}
@if ($message)
    <div role="status" {{ $attributes->class([
        'flex items-center gap-2 rounded-md border border-emerald-500/30 bg-emerald-500/10 px-4 py-2.5',
        'text-sm font-semibold text-emerald-700 dark:text-emerald-300',
    ]) }}>
        <x-ui.icon name="check_circle" size="text-xl" />
        <span>{{ $message }}</span>
    </div>
@endif
