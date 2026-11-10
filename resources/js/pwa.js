import Alpine from 'alpinejs';

/**
 * App instalável + notificações push.
 *
 * O service worker (public/sw.js) fica na raiz para controlar o site todo:
 * é ele que recebe o push do servidor e mostra a notificação com a API de
 * Notificações, mesmo com o app fechado.
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

const isIos = () => /iphone|ipad|ipod/i.test(navigator.userAgent);
const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;

/** applicationServerKey precisa ir como bytes, e a chave VAPID chega em base64url. */
const base64UrlToBytes = (value) => {
    const base64 = (value + '='.repeat((4 - (value.length % 4)) % 4)).replace(/-/g, '+').replace(/_/g, '/');

    return Uint8Array.from(atob(base64), (char) => char.charCodeAt(0));
};

/** A inscrição foi feita com a chave VAPID atual? (Trocar as chaves no servidor invalida as antigas.) */
const usesKey = (subscription, publicKey) => {
    const current = subscription.options?.applicationServerKey;
    if (!current) return true;

    const expected = base64UrlToBytes(publicKey);
    const actual = new Uint8Array(current);

    return actual.length === expected.length && actual.every((byte, index) => byte === expected[index]);
};

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/**
 * Botão "Ativar notificações" do sino.
 *
 * @param {{publicKey: string, subscribeUrl: string, syncedEndpoint: string|null}} config
 */
Alpine.data('pushNotifications', ({ publicKey, subscribeUrl, syncedEndpoint }) => ({
    supported: 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window,

    /** No iOS o push só existe com o app instalado na Tela de Início. */
    needsInstall: isIos() && !isStandalone() && !('PushManager' in window),

    permission: 'Notification' in window ? Notification.permission : 'default',
    subscribed: false,
    busy: false,
    error: null,

    /**
     * Sincroniza a inscrição deste navegador com o servidor: após um novo
     * login (o logout a remove do servidor) ou se o navegador renovou o endpoint.
     */
    async init() {
        if (!this.supported) return;

        try {
            const registration = await navigator.serviceWorker.ready;
            let subscription = await registration.pushManager.getSubscription();

            if (subscription && !usesKey(subscription, publicKey)) {
                await subscription.unsubscribe();
                subscription = null;
            }

            if (subscription && this.permission === 'granted' && subscription.endpoint !== syncedEndpoint) {
                await this.save(subscription);
            }

            this.subscribed = Boolean(subscription) && this.permission === 'granted';
        } catch {
            this.subscribed = false;
        }
    },

    /** Precisa vir de um clique: os navegadores só mostram o pedido de permissão após um gesto. */
    async enable() {
        this.busy = true;
        this.error = null;

        try {
            this.permission = await Notification.requestPermission();
            if (this.permission !== 'granted') return;

            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: base64UrlToBytes(publicKey),
            });

            await this.save(subscription);
            this.subscribed = true;

            registration.showNotification('Notificações ativadas', {
                body: 'Você vai receber avisos do UniFocus neste dispositivo.',
                icon: '/images/pwa/icon-192.png',
                badge: '/images/pwa/badge-96.png',
                tag: 'push-enabled',
            });
        } catch {
            this.error = 'Não foi possível ativar as notificações. Tente novamente.';
        } finally {
            this.busy = false;
        }
    },

    async disable() {
        this.busy = true;
        this.error = null;

        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();

            if (subscription) {
                await this.request('DELETE', { endpoint: subscription.endpoint });
                await subscription.unsubscribe();
            }

            this.subscribed = false;
        } catch {
            this.error = 'Não foi possível desativar as notificações. Tente novamente.';
        } finally {
            this.busy = false;
        }
    },

    async save(subscription) {
        await this.request('POST', subscription.toJSON());
        syncedEndpoint = subscription.endpoint;
    },

    async request(method, body) {
        const response = await fetch(subscribeUrl, {
            method,
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify(body),
        });

        if (!response.ok) throw new Error(`Falha ao sincronizar a inscrição push (${response.status}).`);
    },
}));
