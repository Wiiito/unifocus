/**
 * Captura a posição do GPS do navegador uma vez e envia para o servidor
 * (UserLocationController). O dono do registro é sempre quem está logado,
 * por isso não há user_id aqui.
 *
 * Uso:
 *   new window.LocationTracker({ onSuccess: (data) => ..., onError: (err) => ... }).captureOnce();
 */
const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export class LocationTracker {
    constructor({ endpoint = '/locations', source = 'webview_gps', onSuccess, onError } = {}) {
        this.endpoint = endpoint;
        this.source = source;
        this.onSuccess = onSuccess || (() => {});
        this.onError = onError || ((err) => console.error(err));
    }

    captureOnce(options = {}) {
        if (!('geolocation' in navigator)) {
            this.onError(new Error('Geolocalização não suportada neste dispositivo.'));

            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => this.send(position),
            (geoError) => this.onError(new Error(`Erro de GPS (${geoError.code}): ${geoError.message}`)),
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
                ...options,
            },
        );
    }

    async send(position) {
        try {
            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    source: this.source,
                }),
            });

            const json = await response.json().catch(() => ({}));

            if (!response.ok || !json.ok) {
                throw new Error(json.message || 'Falha ao enviar localização.');
            }

            this.onSuccess(json.data);
        } catch (err) {
            this.onError(err);
        }
    }
}
