<?php

namespace App\Http\Controllers;

use App\Actions\Friendships\AcceptFriendRequest;
use App\Actions\Friendships\SendFriendRequest;
use App\Http\Requests\FriendRequestRequest;
use App\Models\Friendship;
use App\Models\User;
use App\Support\Locations\NearbyStudents;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Amigos do estudante: lista com o foguinho de cada um, pedidos recebidos e
 * enviados. Todo acesso a uma amizade é filtrado pelo usuário logado, então
 * ninguém mexe no vínculo de outras pessoas.
 */
class FriendshipController extends Controller
{
    public function index(Request $request, NearbyStudents $nearbyStudents): View
    {
        $user = $request->user();

        $friendships = Friendship::query()
            ->accepted()
            ->involving($user)
            ->with(['requester.streak', 'addressee.streak'])
            ->get()
            ->sortBy(fn (Friendship $friendship): string => mb_strtolower($friendship->otherParty($user)->name))
            ->values();
        $received = $user->receivedFriendRequests()->with('requester')->latest()->get();
        $sent = $user->sentFriendRequests()->with('addressee')->latest()->get();

        return view('friends.index', [
            'friendships' => $friendships,
            'received' => $received,
            'sent' => $sent,
            'nearby' => $nearbyStudents->for($user),
            'friendIds' => $friendships->map(fn (Friendship $friendship): int => $friendship->otherParty($user)->id)->all(),
            'pendingIds' => [...$sent->pluck('addressee_id'), ...$received->pluck('requester_id')],
        ]);
    }

    public function store(FriendRequestRequest $request, SendFriendRequest $sendFriendRequest, NearbyStudents $nearbyStudents): RedirectResponse
    {
        $addressee = $request->filled('email')
            ? User::where('email', $request->validated('email'))->firstOrFail()
            : $this->nearbyStudent($request->user(), (int) $request->validated('user_id'), $nearbyStudents);

        $friendship = $sendFriendRequest->handle($request->user(), $addressee);

        return redirect()->route('friends.index')->with('flash', $friendship->isAccepted()
            ? __('Agora vocês são amigos!')
            : __('Pedido de amizade enviado.'));
    }

    /**
     * Pedido sem e-mail só vale para quem aparece na lista de pessoas
     * próximas: assim o user_id não vira um jeito de achar qualquer conta.
     *
     * @throws ValidationException
     */
    private function nearbyStudent(User $user, int $otherUserId, NearbyStudents $nearbyStudents): User
    {
        if (! $nearbyStudents->includes($user, $otherUserId)) {
            throw ValidationException::withMessages(['email' => __('Essa pessoa não está mais perto de você.')]);
        }

        return User::findOrFail($otherUserId);
    }

    /**
     * Aceitar: só o destinatário de um pedido pendente.
     */
    public function update(Request $request, AcceptFriendRequest $acceptFriendRequest, int $friendship): RedirectResponse
    {
        $pending = $request->user()->receivedFriendRequests()->findOrFail($friendship);

        $acceptFriendRequest->handle($pending);

        return redirect()->route('friends.index')->with('flash', __('Agora vocês são amigos!'));
    }

    /**
     * Recusar, cancelar o pedido enviado ou desfazer a amizade: em todos os
     * casos o vínculo é apagado, e qualquer uma das partes pode fazê-lo.
     */
    public function destroy(Request $request, int $friendship): RedirectResponse
    {
        $record = Friendship::query()->involving($request->user())->findOrFail($friendship);

        $record->delete();

        return redirect()->route('friends.index')->with('flash', __('Amizade removida.'));
    }
}
