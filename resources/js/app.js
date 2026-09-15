import Alpine from 'alpinejs';

/**
 * Tema claro/escuro.
 *
 * O atributo data-theme no <html> é a única chave do tema: o Tailwind está
 * configurado com darkMode: ['selector', '[data-theme="dark"]'], então trocar
 * este atributo já reposiciona todas as cores do design system.
 *
 * O valor inicial é aplicado por um script inline no <head> (evita flash de
 * tema errado); aqui apenas mantemos o estado reativo para a UI.
 */
Alpine.store('theme', {
    current: document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light',

    get isDark() {
        return this.current === 'dark';
    },

    toggle() {
        this.current = this.isDark ? 'light' : 'dark';
        document.documentElement.dataset.theme = this.current;

        try {
            localStorage.setItem('unifocus_theme', this.current);
        } catch {
            // Modo privado / cookies bloqueados: o tema vale só para esta aba.
        }
    },
});

/**
 * Desafios diários (dados mockados no front).
 *
 * Fica num store porque duas telas leem o mesmo estado: o card de desafios no
 * dashboard e o widget "Meta Diária" da sidebar.
 */
Alpine.store('challenges', {
    items: [
        { title: 'Faculdade', frequency: '1x/semana', done: false },
        { title: 'Calendário', frequency: '1x/dia', done: false },
        { title: '3 Questões IA', frequency: 'Revisão assistida', done: false },
    ],

    init() {
        try {
            const saved = JSON.parse(localStorage.getItem('unifocus_challenges') || '[]');
            this.items.forEach((item, index) => (item.done = Boolean(saved[index])));
        } catch {
            // Sem estado salvo: começa com todos os desafios em aberto.
        }
    },

    get total() {
        return this.items.length;
    },

    get completed() {
        return this.items.filter((item) => item.done).length;
    },

    get percentage() {
        return this.total === 0 ? 0 : Math.round((this.completed / this.total) * 100);
    },

    get allDone() {
        return this.total > 0 && this.completed === this.total;
    },

    persist() {
        try {
            localStorage.setItem('unifocus_challenges', JSON.stringify(this.items.map((item) => item.done)));
        } catch {
            // Sem persistência disponível: o progresso vale só para esta sessão.
        }
    },
});

/**
 * Flashcard 3D da revisão diária (baralho mockado no front).
 */
Alpine.data('flashcard', () => ({
    index: 0,
    flipped: false,

    deck: [
        {
            topic: 'Estruturas de Dados',
            tag: 'Flash Card • Revisão Diária',
            question: 'Conceitos fundamentais de Árvores Binárias de Busca e balanceamento AVL. Toque para virar.',
            answer: 'Árvore AVL é uma árvore de busca binária auto-balanceada onde a diferença de altura entre subárvores esquerda e direita não passa de 1 para qualquer nó (Fator de Balanceamento ∈ {-1, 0, 1}).',
        },
        {
            topic: 'Cálculo I',
            tag: 'Flash Card • Limites & Derivadas',
            question: 'Qual é a definição da Regra da Cadeia para diferenciação de funções compostas?',
            answer: "Se f e g são deriváveis, a derivada da função composta (f ∘ g)(x) é dada por: (f ∘ g)'(x) = f'(g(x)) · g'(x).",
        },
        {
            topic: 'Banco de Dados',
            tag: 'Flash Card • Modelagem Relacional',
            question: 'Qual a diferença entre a Primeira (1FN) e a Segunda Forma Normal (2FN)?',
            answer: 'Na 1FN todos os atributos são atômicos. Na 2FN, além de estar na 1FN, nenhum atributo não-chave depende parcialmente de uma chave candidata composta.',
        },
    ],

    get card() {
        return this.deck[this.index];
    },

    flip() {
        this.flipped = !this.flipped;
    },

    reveal() {
        this.flipped = true;
    },

    /** Desvira o card antes de trocar o conteúdo, para não entregar a resposta. */
    next() {
        this.flipped = false;

        setTimeout(() => (this.index = (this.index + 1) % this.deck.length), 300);
    },
}));

window.Alpine = Alpine;

Alpine.start();
