# 🚀 UniFocus Student Platform - Protocolo V.L.A.E.G. & Diretrizes de Desenvolvimento

> **Projeto Interdisciplinar - 3º Semestre de Ciência da Computação**  
> **Identidade:** Piloto do Sistema. Construção determinística, simples e autorregenerativa da aplicação híbrida **UniFocus App**.

---

## 🟢 Protocolo 0: Memória do Projeto (Obrigatório)
Antes de qualquer código ou modificação:
- [`task_plan.md`](file:///c:/Users/david/OneDrive%20-%20FUNDA%C3%87%C3%83O%20MOVIMENTO%20DIREITO%20E%20CIDADANIA/DomHelder/3_Semestre/Projeto%20interdisciplinar/Unifocus/task_plan.md) → Fases, objetivos e checklists do aplicativo.
- [`findings.md`](file:///c:/Users/david/OneDrive%20-%20FUNDA%C3%87%C3%83O%20MOVIMENTO%20DIREITO%20E%20CIDADANIA/DomHelder/3_Semestre/Projeto%20interdisciplinar/Unifocus/findings.md) → Pesquisas, tokens do Stitch, restrições e regras.
- [`progress.md`](file:///c:/Users/david/OneDrive%20-%20FUNDA%C3%87%C3%83O%20MOVIMENTO%20DIREITO%20E%20CIDADANIA/DomHelder/3_Semestre/Projeto%20interdisciplinar/Unifocus/progress.md) → O que foi feito, testes, erros e status atual.

---

## 📱 Invariantes Arquiteturais do Aplicativo (Mobile-First / Híbrido Web & Capacitor)

1. **Stack Tecnológica Simples e Sem Ferramentas Pesadas**:
   - **Frontend:** HTML5 Semântico, CSS3 Moderno (com Variáveis e Neomorfismo) e JavaScript Puro (ES6+ modular).
   - **Distribuição Mobile:** Arquitetura Híbrida / PWA instalável (`manifest.json` + `sw.js`), 100% pronta para empacotamento com **Capacitor** caso deseje gerar `.apk` / `.ipa`.
   - **Sem Frameworks Complexos:** Zero dependência de `node_modules` pesados ou compiladores difíceis de configurar.
2. **Fidelidade Visual Stitch MCP**:
   - Manter 100% de coerência com a identidade visual exportada do Stitch (`UniFocus - Homepage Inicial`).
   - Cores primárias: `#0061a4` (Primary), `#2196f3` (Container), `#2b5bb5` (Secondary), `#f8fafc` (Background).
   - Tipografia: `Hanken Grotesk` (Títulos/Textos) e `JetBrains Mono` (Labels, códigos e métricas).
   - Ícones: `Material Symbols Outlined`.
3. **Ergonomia e UX Mobile**:
   - Respeito às áreas seguras dos aparelhos (`env(safe-area-inset-top)` e `env(safe-area-inset-bottom)`).
   - Alvos de toque acessíveis (mínimo 44x44px).
   - Navegação por abas inferiores (Bottom Navigation) em telas móveis e feedback tátil por vibração (`navigator.vibrate`).
   - Suporte completo a **Tema Claro e Escuro** com persistência no `localStorage`.

---

## 🏗️ Fase 1: B - Visão (e Lógica)
- **Estrela Guia:** Entregar um aplicativo acadêmico funcional, intuitivo e moderno para estudantes universitários organizarem matérias, notas, rotinas e revisões diárias.
- **Fonte da Verdade:** Dados locais no dispositivo (`localStorage`) com persistência determinística e sem perda de estado.
- **Payload de Entrega:** Aplicativo leve, instalável na tela inicial do celular, responsivo e executável em qualquer navegador moderno.

---

## ⚡ Fase 2: L - Link (Conectividade)
- Conexão e sincronização contínua com o **Stitch MCP** para importação de layouts e design systems.
- Configuração centralizada em `.agents/mcp_config.json`.

---

## ⚙️ Fase 3: A - Arquitetura (A Construção em 3 Camadas)
- **Camada 1: Arquitetura (`architecture/` ou Documentação):**
  - Procedimentos e regras de negócio definidos antes do código.
- **Camada 2: Navegação (Decisões & Fluxos):**
  - Roteamento por abas e transições suaves de tela controladas em `js/main.js`.
- **Camada 3: Código & Ferramentas:**
  - Código desacoplado: marcação em `index.html`, regras de estilo em `css/style.css` e lógica de negócio em `js/main.js`.

---

## ✨ Fase 4: E - Estilo (Refinamento e UI)
- Cartões com sombras neomórficas suaves, glassmorphism na barra de navegação, microinterações a 60fps e animação 3D no card de flashcards.

---

## 🛰️ Fase 5: G - Gatilho (Testes e Execução)
- Validação no navegador e em dispositivos móveis.
- Confirmação explícita com o usuário antes de disparar testes visuais ou capturas de tela.
