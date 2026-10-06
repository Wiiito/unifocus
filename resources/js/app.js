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
 * Flashcard 3D da revisão diária. O baralho vem do servidor (questões
 * aprovadas das matérias que o estudante está cursando).
 *
 * @param {Array<{topic: string, tag: string, question: string, answer: string}>} deck
 */
Alpine.data('flashcard', (deck = []) => ({
    index: 0,
    flipped: false,
    deck,

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
