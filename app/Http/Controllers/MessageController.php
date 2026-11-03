<?php

namespace App\Http\Controllers;

use App\Actions\Messages\SendMessage;
use App\Http\Requests\SendMessageRequest;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Chat entre amigos. Não há websocket: a tela de conversa consulta o endpoint
 * poll a cada poucos segundos pedindo só o que veio depois da última
 * mensagem que já tem. Toda rota resolve o outro lado entre os amigos do
 * usuário logado, então não há conversa com quem não é amigo.
 */
class MessageController extends Controller
{
    /** Mensagens carregadas ao abrir a conversa. */
    private const HISTORY_LIMIT = 50;

    public function index(Request $request): View
    {
        $user = $request->user();

        $unread = Message::query()
            ->where('recipient_id', $user->id)
            ->unread()
            ->selectRaw('sender_id, COUNT(*) AS total')
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        return view('messages.index', [
            'friends' => $user->friends()->with('streak')->orderBy('name')->get()
                ->sortByDesc(fn (User $friend): int => (int) ($unread[$friend->id] ?? 0))
                ->values(),
            'unread' => $unread,
        ]);
    }

    public function show(Request $request, int $friend): View
    {
        $user = $request->user();
        $other = $this->friendOf($user, $friend);

        $this->markAsRead($user, $other);

        return view('messages.show', [
            'friend' => $other,
            'messages' => Message::query()
                ->conversation($user, $other)
                ->latest('id')
                ->limit(self::HISTORY_LIMIT)
                ->get()
                ->reverse()
                ->values(),
        ]);
    }

    /**
     * Mensagens novas (id maior que "after"). Receber já conta como ler: a
     * tela está aberta na conversa.
     */
    public function poll(Request $request, int $friend): JsonResponse
    {
        $user = $request->user();
        $other = $this->friendOf($user, $friend);
        $after = max(0, (int) $request->integer('after'));

        $messages = Message::query()
            ->conversation($user, $other)
            ->where('id', '>', $after)
            ->orderBy('id')
            ->limit(100)
            ->get();

        $this->markAsRead($user, $other);

        return response()->json(['messages' => $messages->map(fn (Message $message): array => $this->present($message, $user))]);
    }

    public function store(SendMessageRequest $request, SendMessage $sendMessage, int $friend): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $message = $sendMessage->handle($user, $this->friendOf($user, $friend), $request->validated('body'));

        return $request->wantsJson()
            ? response()->json(['message' => $this->present($message, $user)], 201)
            : redirect()->route('messages.show', $friend);
    }

    private function friendOf(User $user, int $friendId): User
    {
        return $user->friends()->findOrFail($friendId);
    }

    private function markAsRead(User $user, User $friend): void
    {
        Message::query()
            ->where('sender_id', $friend->id)
            ->where('recipient_id', $user->id)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * @return array{id: int, body: string, mine: bool, time: string}
     */
    private function present(Message $message, User $viewer): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'mine' => (int) $message->sender_id === (int) $viewer->id,
            'time' => $message->created_at->format('H:i'),
        ];
    }
}
