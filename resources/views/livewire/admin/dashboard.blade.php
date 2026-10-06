@php
    use App\Enums\FinalStatus;
    use Illuminate\Support\Number;

    $maxSignups = max(1, $signups->max('total'));
    $maxSubject = max(1, $topSubjects->max('ongoing_enrollments_count') ?? 1);
    $totalOutcomes = $outcomes->sum('total');

    /** Cores de status (reservadas: nunca usadas como cor de série). */
    $outcomeStyles = [
        FinalStatus::InProgress->value => ['bar' => 'bg-outline', 'icon' => 'pending'],
        FinalStatus::Approved->value => ['bar' => 'bg-emerald-500', 'icon' => 'check_circle'],
        FinalStatus::Failed->value => ['bar' => 'bg-error', 'icon' => 'cancel'],
        FinalStatus::FailedAbsence->value => ['bar' => 'bg-streak', 'icon' => 'event_busy'],
    ];
@endphp

<div class="flex flex-col gap-md">
    <x-ui.page-header :title="__('Visão geral')" :description="__('Como a plataforma está sendo usada. Métricas recentes consideram os últimos :days dias.', ['days' => $periodDays])" />

    {{-- Números principais --}}
    <section class="grid grid-cols-2 gap-md lg:grid-cols-4" aria-label="{{ __('Números principais') }}">
        <x-admin.stat-card :label="__('Estudantes')" icon="group" :value="Number::format($overview['users'])"
            :hint="__('+:count nos últimos :days dias', ['count' => $overview['new_users'], 'days' => $periodDays])" />
        <x-admin.stat-card :label="__('Estudantes ativos')" icon="person_check" :value="Number::format($overview['active_students'])"
            :hint="__('com matéria em andamento')" />
        <x-admin.stat-card :label="__('Matrículas em andamento')" icon="menu_book" :value="Number::format($overview['ongoing_enrollments'])"
            :hint="__(':subjects matérias · :institutions instituições', ['subjects' => $overview['subjects'], 'institutions' => $overview['institutions']])" />
        <x-admin.stat-card :label="__('Registros recentes')" icon="edit_note" :value="Number::format($engagement['recent_records'])"
            :hint="__('aulas, atividades e notas lançadas')" />
    </section>

    <section class="grid grid-cols-1 gap-md xl:grid-cols-[2fr_1fr]">
        {{-- Cadastros por dia: série única, uma cor (a do primário). --}}
        <x-ui.card>
            <div class="mb-sm flex items-baseline justify-between gap-sm">
                <x-ui.card-title icon="person_add">{{ __('Novos estudantes por dia') }}</x-ui.card-title>
                <span class="font-mono text-sm text-on-surface-variant">{{ __('total') }}: {{ $signups->sum('total') }}</span>
            </div>

            <div class="flex">
                {{-- Eixo: só o máximo e o zero, recessivos. --}}
                <div class="flex h-40 flex-col justify-between pr-2 text-right font-mono text-[0.7rem] text-on-surface-variant" aria-hidden="true">
                    <span>{{ $maxSignups }}</span>
                    <span>0</span>
                </div>

                <div class="flex h-40 flex-1 items-end gap-[2px] border-b border-outline-variant" aria-hidden="true">
                    @foreach ($signups as $day)
                        <div class="group relative flex h-full flex-1 items-end">
                            <div class="w-full rounded-t-[4px] bg-primary transition-opacity group-hover:opacity-80"
                                style="height: {{ $day['total'] > 0 ? max(2, $day['total'] / $maxSignups * 100) : 0 }}%"></div>

                            <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden -translate-x-1/2 whitespace-nowrap rounded-sm
                                        bg-on-surface px-2 py-1 font-mono text-[0.7rem] text-surface shadow-card group-hover:block">
                                {{ $day['date']->format('d/m') }}: {{ $day['total'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-1 flex justify-between pl-6 font-mono text-[0.7rem] text-on-surface-variant" aria-hidden="true">
                <span>{{ $signups->first()['date']->format('d/m') }}</span>
                <span>{{ $signups->last()['date']->format('d/m') }}</span>
            </div>

            {{-- Mesma informação em tabela, para leitores de tela. --}}
            <table class="sr-only">
                <caption>{{ __('Novos estudantes por dia') }}</caption>
                <tr><th>{{ __('Dia') }}</th><th>{{ __('Cadastros') }}</th></tr>
                @foreach ($signups as $day)
                    <tr><td>{{ $day['date']->format('d/m/Y') }}</td><td>{{ $day['total'] }}</td></tr>
                @endforeach
            </table>
        </x-ui.card>

        {{-- Banco de questões --}}
        <x-ui.card>
            <x-ui.card-title icon="quiz" class="mb-sm">{{ __('Banco de questões') }}</x-ui.card-title>

            <dl class="grid grid-cols-2 gap-sm text-sm">
                <div>
                    <dt class="text-on-surface-variant">{{ __('Aprovadas') }}</dt>
                    <dd class="font-mono text-2xl font-extrabold text-primary">{{ $questionBank['approved'] }}</dd>
                </div>
                <div>
                    <dt class="text-on-surface-variant">{{ __('Aguardando moderação') }}</dt>
                    <dd class="font-mono text-2xl font-extrabold text-primary">{{ $questionBank['pending'] }}</dd>
                </div>
                <div>
                    <dt class="text-on-surface-variant">{{ __('Respostas (:days dias)', ['days' => $periodDays]) }}</dt>
                    <dd class="font-mono text-2xl font-extrabold text-primary">{{ Number::format($questionBank['attempts']) }}</dd>
                </div>
                <div>
                    <dt class="text-on-surface-variant">{{ __('Taxa de acerto') }}</dt>
                    <dd class="font-mono text-2xl font-extrabold text-primary">
                        {{ $questionBank['accuracy'] === null ? '—' : Number::format($questionBank['accuracy'], maxPrecision: 1).'%' }}
                    </dd>
                </div>
            </dl>

            @if ($questionBank['pending'] > 0)
                <a href="{{ route('admin.questions.index', ['status' => 'pending']) }}" class="mt-sm text-sm font-semibold text-primary hover:underline">
                    {{ __('Moderar pendentes') }} →
                </a>
            @endif
        </x-ui.card>
    </section>

    <section class="grid grid-cols-1 gap-md lg:grid-cols-2">
        {{-- Matérias mais cursadas: magnitude, uma cor; a identidade fica no ponto colorido + nome. --}}
        <x-ui.card>
            <x-ui.card-title icon="leaderboard" class="mb-sm">{{ __('Matérias mais cursadas') }}</x-ui.card-title>

            @forelse ($topSubjects as $subject)
                <div class="flex flex-col gap-1 py-1.5">
                    <div class="flex items-center justify-between gap-sm text-sm">
                        <span class="flex min-w-0 items-center gap-2 text-on-surface">
                            <x-ui.subject-dot :subject="$subject" />
                            <span class="truncate">{{ $subject->name }}</span>
                        </span>
                        <span class="font-mono font-bold text-on-surface">{{ $subject->ongoing_enrollments_count }}</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-surface-variant">
                        <div class="h-full rounded-full bg-primary" style="width: {{ $subject->ongoing_enrollments_count / $maxSubject * 100 }}%"></div>
                    </div>
                </div>
            @empty
                <x-ui.empty-state icon="leaderboard" :title="__('Sem matrículas em andamento')" />
            @endforelse
        </x-ui.card>

        {{-- Resultados das matrículas: cor de status + ícone + rótulo + número (nunca só a cor). --}}
        <x-ui.card>
            <x-ui.card-title icon="donut_small" class="mb-sm">{{ __('Situação das matrículas') }}</x-ui.card-title>

            @if ($totalOutcomes === 0)
                <x-ui.empty-state icon="donut_small" :title="__('Nenhuma matrícula ainda')" />
            @else
                <div class="flex h-3 w-full gap-[2px] overflow-hidden rounded-full" aria-hidden="true">
                    @foreach ($outcomes->where('total', '>', 0) as $outcome)
                        <div class="{{ $outcomeStyles[$outcome['status']->value]['bar'] }} h-full"
                            style="width: {{ $outcome['total'] / $totalOutcomes * 100 }}%"
                            title="{{ $outcome['status']->label() }}: {{ $outcome['total'] }}"></div>
                    @endforeach
                </div>

                <ul class="mt-md flex flex-col gap-2 text-sm">
                    @foreach ($outcomes as $outcome)
                        <li class="flex items-center justify-between gap-sm">
                            <span class="flex items-center gap-2 text-on-surface">
                                <span class="size-2.5 rounded-full {{ $outcomeStyles[$outcome['status']->value]['bar'] }}" aria-hidden="true"></span>
                                <x-ui.icon :name="$outcomeStyles[$outcome['status']->value]['icon']" size="text-base" class="text-on-surface-variant" />
                                {{ $outcome['status']->label() }}
                            </span>
                            <span class="font-mono text-on-surface">
                                {{ $outcome['total'] }}
                                <span class="text-on-surface-variant">({{ Number::format($outcome['total'] / $totalOutcomes * 100, maxPrecision: 0) }}%)</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </section>

    {{-- Atividade recente (auditoria) --}}
    <x-ui.card>
        <x-ui.card-title icon="history" class="mb-sm">{{ __('Atividade recente') }}</x-ui.card-title>

        @forelse ($recentActivity as $log)
            <div class="flex items-center justify-between gap-sm border-b border-surface-variant py-2 text-sm last:border-0">
                <span class="text-on-surface">{{ $log->summary() }}</span>
                <span class="shrink-0 font-mono text-xs text-on-surface-variant" title="{{ $log->created_at->format('d/m/Y H:i') }}">
                    {{ $log->created_at->diffForHumans() }}
                </span>
            </div>
        @empty
            <p class="text-sm text-on-surface-variant">{{ __('Nenhuma alteração registrada ainda.') }}</p>
        @endforelse
    </x-ui.card>
</div>
