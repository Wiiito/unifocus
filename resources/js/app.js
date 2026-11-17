import Alpine from 'alpinejs';
import './pwa';

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

const CALENDAR_MONTHS = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
];

const CALENDAR_WEEKDAYS = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

/**
 * Estado e utilitários compartilhados pelos calendários mensais (por matéria e agenda geral).
 * Os campos extras são mesclados antes dos getters no mesmo objeto literal para que os
 * getters permaneçam reativos (spread de um objeto com getters os avaliaria cedo demais).
 */
const createCalendarBase = ({ initialYear, initialMonth, today, resolveItems, collectDates, extra = {} }) => ({
    ...extra,
    view: 'calendar',
    year: initialYear,
    month: initialMonth,
    today,
    selectedDate: null,
    months: CALENDAR_MONTHS,
    weekdays: CALENDAR_WEEKDAYS,

    init() {
        if (resolveItems(this, this.today).length > 0) {
            this.selectedDate = this.today;
        }

        if (typeof this.$nextTick === 'function') {
            this.$nextTick(() => {
                const m = this.month;
                const y = this.year;
                this.month = m;
                this.year = y;
            });
        }
    },

    get yearOptions() {
        const years = new Set([this.year, Number(this.today.slice(0, 4))]);
        collectDates(this).forEach((iso) => {
            if (iso) {
                years.add(Number(iso.slice(0, 4)));
            }
        });

        const min = Math.min(...years) - 1;
        const max = Math.max(...years) + 1;

        return Array.from({ length: max - min + 1 }, (_, i) => min + i);
    },

    prevMonth() {
        if (this.month === 0) {
            this.month = 11;
            this.year -= 1;
        } else {
            this.month -= 1;
        }
    },

    nextMonth() {
        if (this.month === 11) {
            this.month = 0;
            this.year += 1;
        } else {
            this.month += 1;
        }
    },

    toIso(year, monthIndex, day) {
        const m = String(monthIndex + 1).padStart(2, '0');
        const d = String(day).padStart(2, '0');

        return `${year}-${m}-${d}`;
    },

    get cells() {
        const firstDay = new Date(this.year, this.month, 1);
        const startOffset = firstDay.getDay(); // Domingo = 0
        const daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
        const prevMonthDays = new Date(this.year, this.month, 0).getDate();
        const grid = [];

        for (let i = startOffset - 1; i >= 0; i--) {
            const day = prevMonthDays - i;
            const prevDate = new Date(this.year, this.month - 1, day);
            grid.push(this.buildCell(prevDate.getFullYear(), prevDate.getMonth(), day, false));
        }

        for (let day = 1; day <= daysInMonth; day++) {
            grid.push(this.buildCell(this.year, this.month, day, true));
        }

        const remaining = (7 - (grid.length % 7)) % 7;
        for (let day = 1; day <= remaining; day++) {
            const nextDate = new Date(this.year, this.month + 1, day);
            grid.push(this.buildCell(nextDate.getFullYear(), nextDate.getMonth(), day, false));
        }

        return grid;
    },

    buildCell(year, monthIndex, day, inMonth) {
        const iso = this.toIso(year, monthIndex, day);
        const items = resolveItems(this, iso);

        return {
            iso,
            day,
            inMonth,
            isToday: iso === this.today,
            count: items.length,
            hasOverdue: items.some((item) => item.isOverdue),
            hasExam: items.some((item) => item.category === 'exam'),
            hasDelivery: items.some((item) => item.category === 'delivery'),
            hasEvent: items.some((item) => item.category === 'event'),
            hasLesson: items.some((item) => item.category === 'lesson'),
        };
    },

    selectDay(cell) {
        if (!cell.inMonth) {
            this.year = Number(cell.iso.slice(0, 4));
            this.month = Number(cell.iso.slice(5, 7)) - 1;
        }
        this.selectedDate = this.selectedDate === cell.iso ? null : cell.iso;
    },

    get selectedItems() {
        return this.selectedDate ? resolveItems(this, this.selectedDate) : [];
    },

    get selectedDateFormatted() {
        if (!this.selectedDate) return '';
        const [y, m, d] = this.selectedDate.split('-');

        return `${d}/${m}/${y}`;
    },
});

/**
 * Calendário interativo da matéria (grade mensal + aba de lembretes).
 */
Alpine.data('subjectCalendar', ({ initialYear, initialMonth, today, activities = [], events = [] }) =>
    createCalendarBase({
        initialYear,
        initialMonth,
        today,
        resolveItems: (state, iso) => [
            ...state.activities.filter((a) => a.date === iso),
            ...state.events.filter((e) => iso >= e.startsOn && iso <= e.endsOn),
        ],
        collectDates: (state) => [
            ...state.activities.map((a) => a.date),
            ...state.events.flatMap((e) => [e.startsOn, e.endsOn]),
        ],
        extra: { activities, events },
    })
);

/**
 * Agenda geral do estudante (todas as matérias, filtros, calendário mensal e lembretes).
 */
Alpine.data('generalAgenda', ({ initialYear, initialMonth, today, activities = [], lessons = [], events = [] }) =>
    createCalendarBase({
        initialYear,
        initialMonth,
        today,
        resolveItems: (state, iso) => {
            const dayActivities = state.activities.filter((a) => a.date === iso && state.matchesItem(a));
            const dayLessons = state.lessons.filter((l) => l.date === iso && state.matchesItem(l));
            const dayEvents = state.events.filter((e) => iso >= e.startsOn && iso <= e.endsOn && state.matchesItem(e));

            return [...dayActivities, ...dayLessons, ...dayEvents];
        },
        collectDates: (state) => [
            ...state.activities.map((a) => a.date),
            ...state.lessons.map((l) => l.date),
            ...state.events.flatMap((e) => [e.startsOn, e.endsOn]),
        ],
        extra: {
            selectedSubject: 'all',
            selectedCategory: 'all',
            activities,
            lessons,
            events,

            matchesItem(item) {
                if (this.selectedCategory === 'deadlines' && !['exam', 'delivery'].includes(item.category)) {
                    return false;
                }
                if (this.selectedCategory === 'lessons' && item.category !== 'lesson') {
                    return false;
                }
                if (this.selectedCategory === 'events' && item.category !== 'event') {
                    return false;
                }

                if (this.selectedSubject !== 'all') {
                    if (item.kind === 'event') {
                        return true;
                    }

                    return Number(item.subjectId) === Number(this.selectedSubject);
                }

                return true;
            },

            matchesSection(section) {
                return this.selectedCategory === 'all' || this.selectedCategory === section;
            },

            matchesSubject(subjectId) {
                return this.selectedSubject === 'all' || Number(subjectId) === Number(this.selectedSubject);
            },
        },
    })
);

window.Alpine = Alpine;

Alpine.start();
