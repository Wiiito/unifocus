import Alpine from 'alpinejs';

/**
 * App instalável (PWA).
 *
 * O service worker (public/sw.js) fica na raiz para controlar o site todo.
 */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Sem HTTPS (fora do localhost) o navegador recusa o registro; o site segue funcionando.
        });
    });
}

/**
 * Instalação: o Chrome/Edge disparam beforeinstallprompt quando o app é
 * instalável; guardamos o evento para oferecer "Instalar o app" no menu.
 */
Alpine.store('pwa', {
    deferredPrompt: null,
    canInstall: false,

    async install() {
        if (!this.deferredPrompt) return;

        this.deferredPrompt.prompt();
        await this.deferredPrompt.userChoice;
        this.deferredPrompt = null;
        this.canInstall = false;
    },
});

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    Alpine.store('pwa').deferredPrompt = event;
    Alpine.store('pwa').canInstall = true;
});

window.addEventListener('appinstalled', () => {
    Alpine.store('pwa').canInstall = false;
});
