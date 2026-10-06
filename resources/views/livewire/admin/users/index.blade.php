<div class="flex flex-col gap-md">
    <x-ui.page-header :title="__('Estudantes')" :description="__('Consulta das contas. Os dados acadêmicos pertencem a cada estudante e não são editados pelo painel.')" />

    <x-admin.search-input :placeholder="__('Buscar por nome ou e-mail')" />

    <x-admin.table :paginator="$this->users">
        <x-slot:head>
            <th class="px-4 py-3">{{ __('Estudante') }}</th>
            <th class="px-4 py-3">{{ __('Instituições') }}</th>
            <th class="px-4 py-3">{{ __('Matérias em andamento') }}</th>
            <th class="px-4 py-3">{{ __('Questões respondidas') }}</th>
            <th class="px-4 py-3">{{ __('Foguinho') }}</th>
            <th class="px-4 py-3">{{ __('Cadastro') }}</th>
        </x-slot:head>

        @forelse ($this->users as $user)
            <tr wire:key="user-{{ $user->id }}" class="border-b border-card-border last:border-0">
                <td class="px-4 py-3">
                    <span class="block font-semibold text-on-surface">{{ $user->name }}</span>
                    <span class="text-xs text-on-surface-variant">{{ $user->email }}</span>
                </td>
                <td class="px-4 py-3 text-on-surface-variant">{{ $user->institutions->pluck('name')->join(', ') ?: '—' }}</td>
                <td class="px-4 py-3 font-mono text-on-surface-variant">{{ $user->ongoing_enrollments_count }}</td>
                <td class="px-4 py-3 font-mono text-on-surface-variant">{{ $user->question_attempts_count }}</td>
                <td class="px-4 py-3"><x-streak.flame :count="$user->streak?->activeCount() ?? 0" /></td>
                <td class="px-4 py-3 font-mono text-on-surface-variant">{{ $user->created_at->format('d/m/Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-10 text-center text-on-surface-variant">{{ __('Nenhum estudante encontrado.') }}</td>
            </tr>
        @endforelse
    </x-admin.table>
</div>
