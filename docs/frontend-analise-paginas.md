# Análise do Front-End — UniFocus

> Documento de referência e mapa de localização para qualquer desenvolvedor ou agente de IA (Cursor, Claude Code, Codex, Antigravity, Copilot, etc.) trabalhar no front-end do **UniFocus** sem se perder ou quebrar o design system existente.

---

## 1. Stack e Como o Front-End Funciona

- **Backend / Templates:** Laravel 13 + Blade (`resources/views/**/*.blade.php`)
- **Estilização:** Tailwind CSS v3 (`tailwindcss: ^3.1.0` via PostCSS em `postcss.config.js`) + `@tailwindcss/forms`
- **Interatividade (Área do Estudante):** Alpine.js v3 (`resources/js/app.js`) + PWA / Web Push (`resources/js/pwa.js`)
- **Interatividade (Painel Admin):** Livewire v4 (`resources/views/livewire/admin/**`), que já embute sua própria instância de Alpine.js (por isso `layouts/admin.blade.php` carrega apenas `@vite('resources/css/app.css')` e **não** carrega `app.js`).
- **Build de Assets (Vite):** `vite.config.js` compila `resources/css/app.css` e `resources/js/app.js` para `public/build/`.
- **Ambiente Docker:**
  - App Laravel: `http://localhost:8080` (`unifocus_nginx` + `unifocus_app`)
  - WhoDB (PostgreSQL): `http://localhost:8081`
  - **Importante:** se o Vite dev server (`npm run dev`) não estiver rodando, qualquer alteração de classe Tailwind nas views **exige** rodar `docker compose exec -T app npm run build` para atualizar `public/build/`.

---

## 2. Design System e Armadilhas do Tema Claro / Noturno

### 2.1 Como o Tema Funciona
- O tema é controlado pelo atributo `data-theme="light"` ou `data-theme="dark"` na tag `<html>`.
- A definição inicial roda inline no `<head>` dos layouts (`app.blade.php`, `guest.blade.php`, `public.blade.php`, `admin.blade.php`), lendo `localStorage.getItem('unifocus_theme')` ou `prefers-color-scheme`.
- A troca em tempo real é feita por `Alpine.store('theme').toggle()` em `resources/js/app.js`.
- No `tailwind.config.js`: `darkMode: ['selector', '[data-theme="dark"]']`.

### 2.2 Tokens de Cor (`resources/css/app.css`)
Os tokens Material 3 são definidos em `:root` (claro) e `[data-theme='dark']` (noturno):

| Token | Modo Claro (`:root`) | Modo Noturno (`[data-theme='dark']`) | Observação importante |
| :--- | :--- | :--- | :--- |
| `--primary` | `#0061a4` (azul escuro) | `#9ecaff` (azul claro pastel) | **Atenção:** no modo noturno `primary` fica claro! |
| `--secondary` | `#2b5bb5` (azul forte) | `#b0c6ff` (azul/lilás claro) | **Atenção:** no modo noturno `secondary` fica claro! |
| `--on-primary` | `#ffffff` (branco) | `#003258` (azul marinho escuro) | Deve ser usado sobre fundos `bg-primary` |
| `--primary-container` | `#2196f3` | `#00497d` | — |
| `--background` | `#f8fafc` | `#0f172a` (`slate-900`) | Fundo geral da página |
| `--surface` / `--card-bg` | `#ffffff` | `#1e293b` (`slate-800`) | Fundo padrão dos cards (`bg-card`) |
| `--on-surface` | `#191c1e` | `#f1f5f9` | Texto principal |
| `--on-surface-variant` | `#404752` | `#94a3b8` | Texto secundário |
| `--streak-fire` | `#f97316` | `#fb923c` | Cor do foguinho / streak |

### 2.3 Armadilhas Conhecidas para Quem Vai Vibe Codar
1. **Tailwind v3 + CSS variables em hex não aceita opacidade (`/15`, `/20`):**
   - Como os tokens em `app.css` são hexadecimais (`#0061a4`), classes como `bg-primary/15` ou `bg-on-primary/20` **não** aplicam transparência corretamente no Tailwind v3.
   - Para fundos translúcidos em modo escuro, use cores nativas do Tailwind com `dark:`, por exemplo: `dark:bg-slate-900/20` e `dark:text-slate-900`.
2. **Contraste invertido no modo noturno sobre `from-primary to-secondary`:**
   - Em `light`, `from-primary to-secondary` é azul escuro (pede texto branco `text-white`).
   - Em `dark`, `from-primary to-secondary` vira azul pastel claro (`#9ecaff` → `#b0c6ff`), então `text-white` e `bg-white/15` somem! É obrigatório ajustar com `dark:text-slate-900` / `dark:bg-slate-900/20` ou trocar o gradiente no modo `dark:`.
3. **Fontes:** `Hanken Grotesk` (sans), `JetBrains Mono` (mono) e `Material Symbols Outlined` (ícones via `<x-ui.icon name="..." />`).

---

## 3. Mapa Completo das Páginas e Layouts Já Criados

### 3.1 Layouts Base (`resources/views/layouts/`)
| Arquivo | Onde é usado | Características principais |
| :--- | :--- | :--- |
| `layouts/app.blade.php` | Todas as páginas autenticadas do estudante | Grid responsivo (`md:grid-cols-[220px_1fr] lg:grid-cols-[260px_1fr]`). Inclui `header`, `sidebar`, `x-ui.flash` e `bottom-nav`. No `dashboard` mobile, remove o padding superior padrão para o banner encostar no topo. |
| `layouts/guest.blade.php` | `welcome`, `login`, `register`, `forgot-password`, `reset-password`, `confirm-password`, `verify-email`, `admin.login` | Centralizado com logo UniFocus + "Student Platform" e um card (`max-w-md`). Não possui botão de troca de tema visível. |
| `layouts/public.blade.php` | `subjects.index`, `subjects.show` | Cabeçalho simples (`max-w-[960px]`) com logo e botão "Ir para o dashboard" / "Entrar". |
| `layouts/admin.blade.php` | Painel administrativo (`/staff/*`) | Cabeçalho sticky + navegação horizontal no mobile + sidebar fixa no desktop. Usa Alpine do Livewire (sem `app.js`). |

### 3.2 Partials de Navegação (`resources/views/layouts/partials/`)
- `header.blade.php`: Cabeçalho fixo (`68px + safe-area`). Na rota `dashboard`, fica **oculto no mobile** (`hidden md:flex`) porque o `dashboard` tem sua própria topbar mobile dentro do banner. Inclui avatar inicial, saudação, foguinho (`x-streak.flame`), sino de notificações, botão de tema (`sm:flex`) e dropdown de configurações (`Perfil`, tema no mobile, `Instalar o app`, `Sair`).
- `sidebar.blade.php`: Barra lateral fixa no desktop (`hidden md:flex`) com links (`Início`, `Matérias`, `Agenda`, `Boletim`, `Praticar`, `Amigos`, `Mensagens`) e card de `Meta Diária` no rodapé.
- `bottom-nav.blade.php`: Barra inferior flutuante exclusiva do mobile (`md:hidden`) em formato pílula com curva orgânica central e botão `+` que abre a gaveta `"Mais opções"` (`Boletim`, `Amigos`, `Mensagens`). Possui estilo noturno próprio (`dark:bg-[#191b24]`) e ocultamento automático ao rolar para baixo.
- `notification-bell.blade.php`: Sino com badge de não lidas, painel dropdown/popover e controles de Web Push (`pushNotifications`).
- `pwa.blade.php`: Meta tags PWA (`manifest.webmanifest`, `apple-touch-icon`).

### 3.3 Páginas Públicas e Autenticação
| Página / Rota | Arquivo | Estado Atual / Observações |
| :--- | :--- | :--- |
| `/` (Landing) | `resources/views/welcome.blade.php` | Card simples centralizado dentro de `x-guest-layout` com título, subtítulo e botões `Entrar` / `Criar conta` (ou `Ir para o dashboard` se logado). Bem minimalista. |
| `/subjects` | `resources/views/subjects/index.blade.php` | Grid responsivo de cards de matérias públicas com dot colorido, código, créditos e paginação. |
| `/subjects/{subject}` | `resources/views/subjects/show.blade.php` | Detalhes de uma matéria pública (código, créditos, carga horária, descrição). |
| `/login`, `/register`, etc. | `resources/views/auth/*.blade.php` | Telas Breeze adaptadas ao `x-guest-layout`, incluindo `<x-oauth-buttons />` (Google). |
| `/staff/login` | `resources/views/admin/auth/login.blade.php` | Login administrativo em `x-guest-layout`. |

### 3.4 Páginas do Estudante (Autenticadas)
| Página / Rota | Arquivo | Estrutura e Pontos de Atenção |
| :--- | :--- | :--- |
| `/dashboard` (Desktop + Mobile) | `resources/views/dashboard.blade.php` | **Possui dois layouts separados no mesmo arquivo:**<br>1. **Desktop (`hidden md:flex`):** 3 linhas com `streaks`, `grades`, `flashcard`, `upcoming` e `challenges`.<br>2. **Mobile (`flex md:hidden`):** Banner superior azul (`from-primary to-secondary`) com topbar própria (engrenagem, logo, botão de tema), avatar central com badge de foguinho e pill `"Sua vida universitária em foco"`, seguido de um *sheet* arredondado (`rounded-t-[32px]`) com Disciplinas (4 cards coloridos), Seus Horários, Streaks/Desafios e Flashcard.<br>**Observações de UI (Modo Noturno):**<br>- Engrenagem, botão de tema, pill `"Sua vida universitária em foco"`, título `UniFocus` e moldura do avatar **já ajustados** com classes `dark:` escuras (`dark:text-slate-900` / `dark:bg-slate-900/...`).<br>- Verso do Flashcard 3D (`components/dashboard/flashcard.blade.php`) também **já ajustado** para alto contraste no modo noturno. |
| `/enrollments` | `resources/views/enrollments/index.blade.php` | Listagem em grid 2 colunas de `x-academic.enrollment-card` separada em "Em andamento" e "Histórico". |
| `/enrollments/create` e `edit` | `resources/views/enrollments/create.blade.php`, `edit.blade.php` | Formulários de matrícula (`partials/form.blade.php`, `weight-field`, `weight-badge`) + card de exclusão na edição. |
| `/enrollments/{id}` | `resources/views/enrollments/show.blade.php` | Página detalhada da matéria: cards de Pontos (com `x-ui.progress` e regras de aprovação), Faltas, **Calendário da matéria (`x-academic.subject-calendar`, com grade mensal `Dom–Sáb` e alternador para janela de Lembretes reorganizada)**, além de listas/modais de Aulas (`lessons/form`), Atividades (`activities/form`) e Notas (`grade-entries/form`). |
| `/agenda` | `resources/views/agenda/index.blade.php` | Agenda geral de todas as matérias em andamento (`x-academic.general-agenda`): cabeçalho com alternador **Calendário** / **Lembretes**, barra de filtros por matéria e categoria (`Tudo`, `Provas e entregas`, `Aulas`, `Avisos`), grade mensal `Dom – Sáb` com painel do dia selecionado ao lado (desktop) ou abaixo (mobile) e visão `Lembretes` separada em `Atrasadas`, `Próximas provas e entregas`, `Próximas aulas` e `Avisos e calendário dos períodos`. |
| `/report-card` | `resources/views/report-card/index.blade.php` | Boletim agrupado por período letivo em tabela responsiva (`overflow-x-auto`) com pontos, mínimo, faltas e badge de situação. |
| `/practice` | `resources/views/practice/show.blade.php` | Filtro por matéria, enunciado, alternativas em `<label>` estilizado com `has-[:checked]`, ou resposta dissertativa + tela de gabarito comentado pós-resposta. |
| `/friends` | `resources/views/friends/index.blade.php` | Formulário de envio por e-mail, pedidos recebidos (Aceitar/Recusar), lista de amigos com `x-streak.flame` e pedidos enviados. |
| `/messages` e `/messages/{friend}` | `resources/views/messages/index.blade.php`, `show.blade.php` | Lista de conversas com contador de não lidas e chat com polling (`fetch` a cada 3s) + balões `bg-primary text-on-primary` (enviadas) e `bg-surface-container` (recebidas). |
| `/profile` | `resources/views/profile/edit.blade.php` | Cards para dados do perfil, instituições (`institutions.blade.php`), troca de senha e exclusão da conta. |

### 3.5 Painel Administrativo (`/staff/*` — Livewire)
- `livewire/admin/dashboard.blade.php`: 4 `stat-card`s, gráfico de barras de novos cadastros por dia, ranking de matérias e barra de status.
- `livewire/admin/institutions/manager.blade.php`
- `livewire/admin/academic-terms/manager.blade.php`
- `livewire/admin/subjects/manager.blade.php`
- `livewire/admin/questions/manager.blade.php`
- `livewire/admin/users/index.blade.php`

### 3.6 Componentes Reutilizáveis (`resources/views/components/`)
- **UI Base (`components/ui/`):** `badge`, `bottom-nav-link`, `button`, `card`, `card-title`, `empty-state`, `field`, `flash`, `icon`, `page-header`, `progress`, `sidebar-link`, `subject-dot`.
- **Dashboard (`components/dashboard/`):** `challenges`, `flashcard`, `grades`, `streaks`, `upcoming`.
- **Acadêmicos / Sociais:** `academic/agenda-item`, `academic/enrollment-card`, `friends/avatar`, `streak/flame`.
- **Admin:** `admin/form-modal`, `admin/row-actions`, `admin/search-input`, `admin/stat-card`, `admin/table`.
- **Breeze / Auth:** `application-logo`, `auth-session-status`, `danger-button`, `dropdown`, `dropdown-link`, `input-error`, `input-label`, `modal`, `nav-link`, `oauth-buttons`, `primary-button`, `responsive-nav-link`, `secondary-button`, `select-input`, `text-input`, `textarea-input`.

---

## 4. Referência Stitch
- Projeto no Stitch MCP: **UniFocus Student Platform** (`projects/10258873874611402157`).
- Telas presentes no projeto Stitch: `UniFocus - Homepage Inicial`, `Homepageinicial.png`, `Paletasdecores.png`, `Unifocus.png`, `image.png`.

