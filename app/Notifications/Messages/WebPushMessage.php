<?php

namespace App\Notifications\Messages;

use Illuminate\Support\Str;

/**
 * Conteúdo de uma notificação: o mesmo texto vai para o sino (canal
 * database) e para o dispositivo (WebPushChannel).
 */
final readonly class WebPushMessage
{
    /**
     * @param  string  $symbol  Ícone Material Symbols exibido no sino.
     * @param  string|null  $tag  Notificações com a mesma tag se substituem no aparelho.
     * @param  'very-low'|'low'|'normal'|'high'  $urgency
     */
    public function __construct(
        public string $title,
        public string $body,
        public string $url,
        public string $symbol = 'notifications',
        public ?string $tag = null,
        public string $urgency = 'normal',
    ) {}

    public function withUrl(string $url): self
    {
        return new self($this->title, $this->body, $url, $this->symbol, $this->tag, $this->urgency);
    }

    /**
     * Cabeçalho Topic: com ele o serviço de push guarda só a última mensagem
     * da mesma tag enquanto o aparelho está offline. Aceita até 32 caracteres
     * base64url.
     */
    public function topic(): ?string
    {
        return $this->tag !== null && preg_match('/^[A-Za-z0-9_-]{1,32}$/', $this->tag) ? $this->tag : null;
    }

    /**
     * O que o service worker recebe (public/sw.js).
     *
     * @return array{title: string, body: string, url: string, tag: string|null, icon: string, badge: string}
     */
    public function toPayload(): array
    {
        return [
            'title' => Str::limit($this->title, 80),
            'body' => Str::limit($this->body, 180),
            'url' => $this->url,
            'tag' => $this->tag,
            'icon' => asset('images/pwa/icon-192.png'),
            'badge' => asset('images/pwa/badge-96.png'),
        ];
    }

    /**
     * O que fica gravado no canal database.
     *
     * @return array{title: string, body: string, url: string, symbol: string}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'symbol' => $this->symbol,
        ];
    }
}
