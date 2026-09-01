# 📋 Task Plan - UniFocus Mobile App

## 🎯 Objetivo Geral
Desenvolver e estruturar a plataforma **UniFocus** como um **Aplicativo Mobile Híbrido (PWA / Capacitor Ready)** simples, leve e determinístico, utilizando HTML5, CSS3 e JavaScript puro, mantendo a identidade visual do Stitch.

---

## 🗂️ Fases e Checklists

### ✅ Fase 0: Inicialização e Protocolos
- [x] Extração e análise da tela do Stitch MCP (`UniFocus - Homepage Inicial`).
- [x] Salvar configuração do MCP em `.agents/mcp_config.json`.
- [x] Atualizar `Gemini.md` com as diretrizes do App e metodologia V.L.A.E.G.
- [x] Criar arquivos de memória: `task_plan.md`, `findings.md` e `progress.md`.

### 🔄 Fase 1: Arquitetura e Configuração de App Mobile (PWA / Capacitor)
- [x] Criar `manifest.json` com configurações de App standalone (ícones, orientação, tema).
- [x] Criar `sw.js` (Service Worker) para cache offline e comportamento de aplicativo nativo.
- [x] Adicionar meta tags mobile e Safe Area Insets no `index.html`.

### 🎨 Fase 2: Interface e Design System Mobile
- [x] Estruturar o layout responsivo com visualização móvel prioritária (Bottom Navigation).
- [x] Aplicar paleta de cores e tokens do Stitch em `css/style.css`.
- [x] Implementar sombras neomórficas e glassmorphism.
- [x] Garantir compatibilidade com `env(safe-area-inset-*)` para iPhone/Android.

### ⚡ Fase 3: Lógica e Interatividade (Camada de Negócios)
- [x] Implementar rotação 3D interativa no card de Flashcards com suporte a múltiplos temas.
- [x] Implementar contador de Desafios Diários com persistência e feedback tátil (`navigator.vibrate`).
- [x] Implementar alternador de Tema Claro / Escuro com persistência no `localStorage`.
- [x] Adicionar Toasts de notificação e navegação fluida entre abas.

### 🧪 Fase 4: Validação e Testes
- [ ] Solicitar confirmação do usuário para rodar testes visuais e capturas de tela.
