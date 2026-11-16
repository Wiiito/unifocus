# Plano e Backup de Alterações do Front-End — UniFocus

> **Objetivo deste arquivo:**
> 1. Planejar e registrar todas as alterações feitas no front-end.
> 2. Guardar o **estado exato anterior (backup)** e o **estado novo** de cada trecho modificado, permitindo reverter facilmente ou continuar o trabalho em qualquer ferramenta de *vibe coding* (Antigravity, Cursor, Claude Code, Codex, Copilot, etc.).

---

## Como Usar Este Backup (Qualquer Plataforma)

1. Antes de editar qualquer view ou CSS do front-end, leia também [`docs/frontend-analise-paginas.md`](./frontend-analise-paginas.md).
2. Ao fazer uma nova alteração:
   - Registre uma nova entrada cronológica abaixo (`## [ALT-XXX] ...`).
   - Copie o trecho **Antes (Backup)** exatamente como estava antes da edição.
   - Cole o trecho **Depois (Atual)** e liste os arquivos afetados + comando de build executado.
3. **Para reverter uma alteração:** basta restaurar o bloco **Antes (Backup)** no arquivo e linha indicados e rodar `docker compose exec -T app npm run build`.
4. **Regra do Tailwind v3:** após alterar qualquer classe em `.blade.php`, `.js` ou `.css`, compile os assets:
   ```bash
   docker compose exec -T app npm run build
   ```

---

## Backlog / Alterações Planejadas (Próximos Passos Sugeridos)

> Itens identificados na análise inicial aguardando confirmação do usuário antes de serem executados:

- [x] **[ALT-001]** Corrigir contraste no modo noturno da engrenagem, ícone de troca de tema e pill `"Sua vida universitária em foco"` no banner mobile de `resources/views/dashboard.blade.php`.
- [x] **[ALT-002]** Corrigir contraste no modo noturno do título `UniFocus` (linha 55) e da moldura do avatar (linha 73) no banner mobile de `resources/views/dashboard.blade.php`.
- [x] **[ALT-003]** Corrigir contraste no modo noturno do verso do Flashcard 3D (`resources/views/components/dashboard/flashcard.blade.php`, linhas 50–65).
- [x] **[ALT-004]** Novo calendário mensal interativo (`Dom – Sáb`) + janela de Lembretes reorganizada em `resources/views/enrollments/show.blade.php` (`x-academic.subject-calendar`).
- [x] **[ALT-005]** Nova página `Agenda` geral (`/agenda`) com calendário mensal de todas as matérias, painel lateral do dia, filtros por matéria/tipo e janela de `Lembretes` organizada em 4 blocos (`app/Support/Students/StudentAgenda.php`, `AgendaController.php`, `general-agenda.blade.php`, `agenda/index.blade.php` e `app.js`).
- [ ] **[PENDENTE-03]** Novas alterações visuais definidas pelo usuário nas demais páginas (`welcome`, `dashboard`, `enrollments`, etc.).

---

## Histórico de Alterações e Backups

### [ALT-001] Contraste no modo noturno do cabeçalho e frase do banner mobile (`dashboard.blade.php`)
- **Data:** 2026-11-16
- **Arquivo alterado:** `resources/views/dashboard.blade.php` (linhas 46–86)
- **Motivo:** No modo noturno (`[data-theme="dark"]`), o banner superior usa `bg-gradient-to-b from-primary to-secondary`, onde `--primary` (`#9ecaff`) e `--secondary` (`#b0c6ff`) são azul-claros. Os botões de configurações (engrenagem), troca de tema e a frase `"Sua vida universitária em foco"` usavam `bg-white/15 text-white`, perdendo legibilidade.
- **Obs. técnica:** Como os tokens de `resources/css/app.css` estão em hexadecimal, o Tailwind v3 não aplica opacidade sobre `var(--on-primary)`. Por isso foram usadas as classes `dark:bg-slate-900/20`, `dark:hover:bg-slate-900/30` e `dark:text-slate-900`, mantendo `bg-white/15 text-white` intacto no modo claro.

#### Antes (Backup)
```html
                <a href="{{ route('profile.edit') }}"
                   title="{{ __('Configurações') }}"
                   aria-label="{{ __('Configurações') }}"
                   class="flex size-11 items-center justify-center rounded-full bg-white/15 text-white shadow-sm backdrop-blur-md transition-all duration-normal ease-bounce hover:scale-110 hover:rotate-45 hover:bg-white/25 hover:shadow-lg active:scale-90">
                    <x-ui.icon name="settings" size="text-2xl" />
                </a>

                <div class="flex items-center gap-2">
                    <x-application-logo class="w-8 drop-shadow-sm" />
                    <span class="text-xl font-extrabold tracking-tight text-white">UniFocus</span>
                </div>

                <button type="button" x-data @click="$store.theme.toggle()"
                    :aria-label="$store.theme.isDark ? '{{ __('Ativar tema claro') }}' : '{{ __('Ativar tema escuro') }}'"
                    :title="$store.theme.isDark ? '{{ __('Tema claro') }}' : '{{ __('Tema escuro') }}'"
                    class="flex size-11 items-center justify-center rounded-full bg-white/15 text-white shadow-sm backdrop-blur-md transition-all duration-normal ease-bounce hover:scale-110 hover:rotate-12 hover:bg-white/25 hover:shadow-lg active:scale-90">
                    <x-ui.icon name="dark_mode" x-show="! $store.theme.isDark" />
                    <x-ui.icon name="light_mode" x-show="$store.theme.isDark" x-cloak />
                </button>
            </header>

            {{-- Imagem de Perfil do Usuário em Destaque Central --}}
            <div class="relative mx-auto mt-4 flex max-w-sm flex-col items-center justify-center px-gutter text-center">
                <a href="{{ route('profile.edit') }}"
                   title="{{ __('Ir para o perfil') }}"
                   aria-label="{{ __('Ir para o perfil de :name', ['name' => $currentUser?->name]) }}"
                   class="relative flex items-center justify-center group cursor-pointer">
                    <div class="relative flex size-28 items-center justify-center overflow-hidden rounded-xl border-2 border-white/30 bg-white/15 p-1.5 shadow-inner backdrop-blur-md transition-all duration-normal ease-bounce group-hover:scale-105 group-hover:border-white/60 group-hover:bg-white/25">
                        <img src="{{ $userAvatar }}"
                             alt="{{ $currentUser?->name }}"
                             class="size-full rounded-lg object-cover shadow-md transition-transform duration-normal group-hover:scale-105" />
                    </div>
                    <div class="absolute -bottom-2 -right-2 flex size-10 items-center justify-center rounded-full bg-streak shadow-lg transition-transform duration-normal ease-bounce group-hover:scale-110 group-hover:rotate-12">
                        <x-ui.icon name="local_fire_department" filled size="text-2xl" class="text-white animate-flame-pulse" />
                    </div>
                </a>

                <span class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold tracking-wide text-white backdrop-blur-sm transition-transform duration-normal hover:scale-105">
```

#### Depois (Atual)
```html
                <a href="{{ route('profile.edit') }}"
                   title="{{ __('Configurações') }}"
                   aria-label="{{ __('Configurações') }}"
                   class="flex size-11 items-center justify-center rounded-full bg-white/15 text-white shadow-sm backdrop-blur-md transition-all duration-normal ease-bounce hover:scale-110 hover:rotate-45 hover:bg-white/25 hover:shadow-lg active:scale-90 dark:bg-slate-900/20 dark:text-slate-900 dark:hover:bg-slate-900/30">
                    <x-ui.icon name="settings" size="text-2xl" />
                </a>

                <div class="flex items-center gap-2">
                    <x-application-logo class="w-8 drop-shadow-sm" />
                    <span class="text-xl font-extrabold tracking-tight text-white">UniFocus</span>
                </div>

                <button type="button" x-data @click="$store.theme.toggle()"
                    :aria-label="$store.theme.isDark ? '{{ __('Ativar tema claro') }}' : '{{ __('Ativar tema escuro') }}'"
                    :title="$store.theme.isDark ? '{{ __('Tema claro') }}' : '{{ __('Tema escuro') }}'"
                    class="flex size-11 items-center justify-center rounded-full bg-white/15 text-white shadow-sm backdrop-blur-md transition-all duration-normal ease-bounce hover:scale-110 hover:rotate-12 hover:bg-white/25 hover:shadow-lg active:scale-90 dark:bg-slate-900/20 dark:text-slate-900 dark:hover:bg-slate-900/30">
                    <x-ui.icon name="dark_mode" x-show="! $store.theme.isDark" />
                    <x-ui.icon name="light_mode" x-show="$store.theme.isDark" x-cloak />
                </button>
            </header>

            {{-- Imagem de Perfil do Usuário em Destaque Central --}}
            <div class="relative mx-auto mt-4 flex max-w-sm flex-col items-center justify-center px-gutter text-center">
                <a href="{{ route('profile.edit') }}"
                   title="{{ __('Ir para o perfil') }}"
                   aria-label="{{ __('Ir para o perfil de :name', ['name' => $currentUser?->name]) }}"
                   class="relative flex items-center justify-center group cursor-pointer">
                    <div class="relative flex size-28 items-center justify-center overflow-hidden rounded-xl border-2 border-white/30 bg-white/15 p-1.5 shadow-inner backdrop-blur-md transition-all duration-normal ease-bounce group-hover:scale-105 group-hover:border-white/60 group-hover:bg-white/25">
                        <img src="{{ $userAvatar }}"
                             alt="{{ $currentUser?->name }}"
                             class="size-full rounded-lg object-cover shadow-md transition-transform duration-normal group-hover:scale-105" />
                    </div>
                    <div class="absolute -bottom-2 -right-2 flex size-10 items-center justify-center rounded-full bg-streak shadow-lg transition-transform duration-normal ease-bounce group-hover:scale-110 group-hover:rotate-12">
                        <x-ui.icon name="local_fire_department" filled size="text-2xl" class="text-white animate-flame-pulse" />
                    </div>
                </a>

                <span class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold tracking-wide text-white backdrop-blur-sm transition-transform duration-normal hover:scale-105 dark:bg-slate-900/20 dark:text-slate-900">
```

- **Build e verificação:**
  - `docker compose exec -T app npm run build` → `public/build/assets/app-El0MMIL0.css`
  - `docker compose exec -T app php artisan test tests/Feature/StudentPagesTest.php` → `4 passed (13 assertions)`


---

### [ALT-002] Contraste no modo noturno do título `UniFocus` e moldura do avatar (`dashboard.blade.php`)
- **Data:** 2026-11-16
- **Arquivo alterado:** `resources/views/dashboard.blade.php` (linhas 53–56 e 73)
- **Motivo:** No banner mobile em modo noturno, o título `UniFocus` (`text-white`) e a moldura do avatar (`border-white/30 bg-white/15`) também ficavam claros sobre o gradiente pastel `from-primary to-secondary`.

#### Antes (Backup)
```html
                <div class="flex items-center gap-2">
                    <x-application-logo class="w-8 drop-shadow-sm" />
                    <span class="text-xl font-extrabold tracking-tight text-white">UniFocus</span>
                </div>
```
```html
                    <div class="relative flex size-28 items-center justify-center overflow-hidden rounded-xl border-2 border-white/30 bg-white/15 p-1.5 shadow-inner backdrop-blur-md transition-all duration-normal ease-bounce group-hover:scale-105 group-hover:border-white/60 group-hover:bg-white/25">
```

#### Depois (Atual)
```html
                <div class="flex items-center gap-2">
                    <x-application-logo class="w-8 drop-shadow-sm" />
                    <span class="text-xl font-extrabold tracking-tight text-white dark:text-slate-900">UniFocus</span>
                </div>
```
```html
                    <div class="relative flex size-28 items-center justify-center overflow-hidden rounded-xl border-2 border-white/30 bg-white/15 p-1.5 shadow-inner backdrop-blur-md transition-all duration-normal ease-bounce group-hover:scale-105 group-hover:border-white/60 group-hover:bg-white/25 dark:border-slate-900/20 dark:bg-slate-900/15 dark:group-hover:border-slate-900/40 dark:group-hover:bg-slate-900/25">
```

---

### [ALT-003] Contraste no modo noturno do verso do Flashcard 3D (`flashcard.blade.php`)
- **Data:** 2026-11-16
- **Arquivo alterado:** `resources/views/components/dashboard/flashcard.blade.php` (linhas 50–65)
- **Motivo:** O verso do card de revisão diária usa `bg-gradient-to-br from-primary to-secondary` com textos brancos e o botão `"Próximo Card"` usando `!bg-white !text-primary`. No modo noturno (`--primary: #9ecaff`), os textos sumiam e o botão ficava com texto azul-claro no fundo branco.

#### Antes (Backup)
```html
            {{-- Verso: resposta --}}
            <div class="backface-hidden rotate-y-180 absolute inset-0 flex flex-col items-center justify-center rounded-lg
                        border border-white/20 bg-gradient-to-br from-primary to-secondary px-md py-lg text-on-primary shadow-card-hover">
                <span class="mb-sm rounded-full bg-white/20 px-3 py-1 font-mono text-xs font-bold uppercase tracking-[0.12em] text-white">
                    {{ __('Resposta • Conceito Chave') }}
                </span>

                <h3 class="mb-sm text-[1.45rem] font-bold text-white md:text-[1.85rem]" x-text="card.topic"></h3>

                <p class="mx-auto mb-md line-clamp-5 max-w-[440px] shrink-0 leading-relaxed text-white/90" x-text="card.answer"></p>

                <x-secondary-button @click.stop="next()" class="!border-white/30 !bg-white !text-primary hover:!bg-white/90">
                    <x-ui.icon name="sync" size="text-xl" />
                    <span>{{ __('Próximo Card') }}</span>
                </x-secondary-button>
            </div>
```

#### Depois (Atual)
```html
            {{-- Verso: resposta --}}
            <div class="backface-hidden rotate-y-180 absolute inset-0 flex flex-col items-center justify-center rounded-lg
                        border border-white/20 bg-gradient-to-br from-primary to-secondary px-md py-lg text-on-primary shadow-card-hover dark:border-slate-900/15">
                <span class="mb-sm rounded-full bg-white/20 px-3 py-1 font-mono text-xs font-bold uppercase tracking-[0.12em] text-white dark:bg-slate-900/20 dark:text-slate-900">
                    {{ __('Resposta • Conceito Chave') }}
                </span>

                <h3 class="mb-sm text-[1.45rem] font-bold text-white dark:text-slate-900 md:text-[1.85rem]" x-text="card.topic"></h3>

                <p class="mx-auto mb-md line-clamp-5 max-w-[440px] shrink-0 leading-relaxed text-white/90 dark:text-slate-900/90" x-text="card.answer"></p>

                <x-secondary-button @click.stop="next()" class="!border-white/30 !bg-white !text-primary hover:!bg-white/90 dark:!border-slate-900/20 dark:!bg-slate-900 dark:!text-white dark:hover:!bg-slate-800">
                    <x-ui.icon name="sync" size="text-xl" />
                    <span>{{ __('Próximo Card') }}</span>
                </x-secondary-button>
            </div>
```

- **Build:** `docker compose exec -T app npm run build` → `public/build/assets/app-B2xZpjFH.css`

---

### [ALT-004] Calendário mensal interativo e aba de Lembretes nas matérias (`enrollments/show.blade.php` + `subject-calendar.blade.php` + `app.js`)
- **Data:** 2026-11-16
- **Arquivos criados/alterados:**
  - Criado: `resources/views/components/academic/subject-calendar.blade.php`
  - Alterado: `resources/views/enrollments/show.blade.php` (linhas 109–125)
  - Alterado: `resources/js/app.js` (adição do componente `Alpine.data('subjectCalendar', ...)`)
- **Motivo:** Substituir a lista solta de eventos do período por um calendário mensal visual (`Dom – Sáb`) com navegação por mês/ano, destaque por cor do tema (Prova, Entrega, Recesso/Evento, Hoje), detalhes do dia ao clicar e um botão no cabeçalho para alternar entre a janela de **Calendário** e a janela de **Lembretes** reorganizada por seções.

#### Antes (Backup — `resources/views/enrollments/show.blade.php`)
```blade
    {{-- Calendário do período (semana de provas, feriados...) --}}
    @if ($term?->events->isNotEmpty())
        <x-ui.card>
            <x-ui.card-title icon="event_note" class="mb-sm">{{ __('Calendário de :term', ['term' => $term->name]) }}</x-ui.card-title>

            <ul class="flex flex-wrap gap-2">
                @foreach ($term->events as $event)
                    <li class="rounded-md border border-surface-variant bg-surface-container-low px-3 py-2 text-sm">
                        <span class="font-semibold text-on-surface">{{ $event->title }}</span>
                        <span class="block font-mono text-xs text-on-surface-variant">
                            {{ $event->starts_on->format('d/m') }}@if (! $event->ends_on->isSameDay($event->starts_on)) – {{ $event->ends_on->format('d/m') }}@endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif
```

#### Depois (Atual — `resources/views/enrollments/show.blade.php`)
```blade
    {{-- Calendário da matéria (provas, entregas e marcos do período) --}}
    <x-academic.subject-calendar :enrollment="$enrollment" :term="$term" :activities="$activities" />
```

- **Para reverter:**
  1. Restaurar o bloco **Antes (Backup)** em `resources/views/enrollments/show.blade.php`.
  2. (Opcional) Remover `resources/views/components/academic/subject-calendar.blade.php` e o bloco `Alpine.data('subjectCalendar', ...)` em `resources/js/app.js`.
  3. Rodar `docker compose exec -T app npm run build`.

---

### [ALT-005] Nova página `Agenda` geral com calendário mensal, filtros e lembretes organizados (`/agenda`)
- **Data:** 2026-11-16
- **Arquivos criados/alterados:**
  - Criado: `resources/views/components/academic/general-agenda.blade.php`
  - Alterado: `resources/views/agenda/index.blade.php`
  - Alterado: `app/Http/Controllers/AgendaController.php`
  - Alterado: `app/Support/Students/StudentAgenda.php` (adição do método `calendarData(User $user)`)
  - Alterado: `app/Enums/TermEventType.php` (adição de `icon(): string`, reutilizado também em `subject-calendar.blade.php`)
  - Alterado: `resources/js/app.js` (extração da base compartilhada `createCalendarBase` e adição de `Alpine.data('generalAgenda', ...)`)
- **Motivo:** Transformar a página `/agenda` em um calendário mensal geral (`Dom – Sáb`) abrangendo todas as matérias/cursos em andamento, com painel do dia selecionado, filtros por matéria e categoria, indicador discreto para aulas, cores do tema para atrasos/provas/entregas/recessos e uma janela de `Lembretes` organizada em 4 seções (`Atrasadas`, `Próximas provas e entregas`, `Próximas aulas` e `Avisos e calendário dos períodos`).

#### Antes (Backup — `resources/views/agenda/index.blade.php`)
```blade
<x-app-layout>
    <x-ui.page-header :title="__('Agenda')"
        :description="__('Prazos, aulas e o calendário dos seus períodos nos próximos :days dias.', ['days' => $daysAhead])" />

    @if ($overdue->isNotEmpty())
        <x-ui.card>
            <x-ui.card-title icon="warning" class="mb-sm !text-error">{{ __('Atrasadas') }}</x-ui.card-title>

            <div class="grid grid-cols-1 gap-sm md:grid-cols-2">
                @foreach ($overdue as $item)
                    <x-academic.agenda-item :item="$item" />
                @endforeach
            </div>
        </x-ui.card>
    @endif

    @forelse ($days as $date => $items)
        @php($day = \Illuminate\Support\Carbon::parse($date))

        <section class="flex flex-col gap-sm" aria-label="{{ $day->translatedFormat('l, d \d\e F') }}">
            <h2 class="flex items-baseline gap-2 text-sm font-bold uppercase tracking-wide text-on-surface-variant">
                <span @class(['text-primary' => $day->isToday()])>
                    {{ $day->isToday() ? __('Hoje') : ($day->isTomorrow() ? __('Amanhã') : $day->translatedFormat('l')) }}
                </span>
                <span class="font-mono font-medium normal-case">{{ $day->format('d/m') }}</span>
            </h2>

            <div class="grid grid-cols-1 gap-sm md:grid-cols-2">
                @foreach ($items as $item)
                    <x-academic.agenda-item :item="$item" :show-date="false" />
                @endforeach
            </div>
        </section>
    @empty
        <x-ui.card>
            <x-ui.empty-state icon="event_available" :title="__('Agenda livre')"
                :description="__('Nada registrado para os próximos dias. Cadastre atividades com prazo e aulas nas suas matérias.')">
                <x-ui.button :href="route('enrollments.index')" variant="secondary">{{ __('Ir para matérias') }}</x-ui.button>
            </x-ui.empty-state>
        </x-ui.card>
    @endforelse
</x-app-layout>
```

#### Antes (Backup — `app/Http/Controllers/AgendaController.php`)
```php
    public function __invoke(Request $request, StudentAgenda $agenda, StreakService $streaks): View
    {
        $user = $request->user();

        $streaks->recordAgendaView($user);

        return view('agenda.index', [
            'overdue' => $agenda->overdue($user),
            'days' => $agenda->between($user, today(), today()->addDays(self::DAYS_AHEAD)->endOfDay())
                ->groupBy(fn (AgendaItem $item) => $item->startsAt->toDateString()),
            'daysAhead' => self::DAYS_AHEAD,
        ]);
    }
```

#### Depois (Atual)
- `resources/views/agenda/index.blade.php` renderiza `<x-academic.general-agenda :calendar="$calendar" :days-ahead="$daysAhead" />`.
- `AgendaController::__invoke` passa `'calendar' => $agenda->calendarData($user)` e `'daysAhead' => self::DAYS_AHEAD`.

- **Para reverter:**
  1. Restaurar `resources/views/agenda/index.blade.php` e `app/Http/Controllers/AgendaController.php` pelos blocos acima.
  2. (Opcional) Remover `resources/views/components/academic/general-agenda.blade.php`, `calendarData()` em `StudentAgenda.php` e `generalAgenda` em `resources/js/app.js`.
  3. Rodar `docker compose exec -T app npm run build`.

---

### [ALT-006] Correção de inicialização do Alpine nos calendários (`app.js`, `subject-calendar.blade.php` e `general-agenda.blade.php`)
- **Data:** 2026-11-16
- **Arquivos alterados:**
  - `resources/js/app.js` (refatoração de `createCalendarBase` para receber `extra = {}`)
  - `resources/views/components/academic/subject-calendar.blade.php` (`:selected` nos `<select>` de mês e ano)
  - `resources/views/components/academic/general-agenda.blade.php` (`:selected` nos `<select>` de mês e ano)
- **Causa do bug:** O spread `{ ...createCalendarBase(...) }` em JavaScript avaliava os *getters* (`yearOptions`, `cells`, `selectedItems`, `selectedDateFormatted`) no momento da criação do objeto, antes de `activities`/`events`/`lessons` existirem, disparando erro de execução (`undefined`) na inicialização do Alpine e impedindo o calendário e a aba Lembretes de carregarem.
- **Correção:** Passado `extra` diretamente para dentro de `createCalendarBase` antes da declaração dos *getters* no mesmo objeto literal, além da sincronização inicial dos `<select>` de mês/ano.
- **Build e verificação:**
  - `docker compose exec -T app npm run build` → `app-BHpR4WDA.css` e `app-CN4fUat-.js`
  - `docker compose exec -T app php artisan test tests/Feature/StudentPagesTest.php tests/Feature/Enrollments` → `39 passed (112 assertions)`
  - Verificação Node do `subjectCalendar` e `generalAgenda` → `scCells: 35`, `gaBeforeCells: 35`, `gaAfterFirstInMonth: "2026-12-01"`
