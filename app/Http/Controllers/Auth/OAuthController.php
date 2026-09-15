<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OauthIdentity;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Socialite;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Fluxo de login OAuth compartilhado por todos os provedores declarados em
 * config/oauth.php. Adicionar um novo login não exige mudanças aqui.
 */
class OAuthController extends Controller
{
    /**
     * Envia o usuário para a tela de consentimento do provedor.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $config = $this->providerConfig($provider);

        return Socialite::driver($provider)
            ->scopes($config['scopes'])
            ->with($config['with'])
            ->redirect();
    }

    /**
     * Recebe o retorno do provedor e autentica a entidade correspondente.
     */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        $config = $this->providerConfig($provider);

        try {
            $providerUser = Socialite::driver($provider)->user();
        } catch (Throwable) {
            return redirect()->route('login')->withErrors([
                'email' => __('Não foi possível concluir o login com :provider. Tente novamente.', [
                    'provider' => $config['label'],
                ]),
            ]);
        }

        $identity = $this->resolveIdentity($provider, $providerUser, $config);

        Auth::guard($config['guard'])->login($identity->authenticatable, remember: true);

        $request->session()->regenerate();

        return redirect()->intended($config['redirect']);
    }

    /**
     * Configuração efetiva do provedor, abortando quando ele não está declarado.
     *
     * @return array<string, mixed>
     */
    protected function providerConfig(string $provider): array
    {
        return OauthIdentity::providers()[$provider]
            ?? throw new NotFoundHttpException("Provedor OAuth [{$provider}] não está habilitado.");
    }

    /**
     * Localiza ou cria a identidade do provedor, mantendo o perfil e os tokens
     * sincronizados a cada login.
     *
     * @param  array<string, mixed>  $config
     */
    protected function resolveIdentity(string $provider, ProviderUser $providerUser, array $config): OauthIdentity
    {
        return DB::transaction(function () use ($provider, $providerUser, $config): OauthIdentity {
            $identity = OauthIdentity::firstOrNew([
                'provider' => $provider,
                'provider_id' => (string) $providerUser->getId(),
            ]);

            if (! $identity->exists) {
                $identity->authenticatable()->associate(
                    $this->findOrCreateAccount($providerUser, $config)
                );
            }

            $identity->fill([
                'name' => $providerUser->getName(),
                'nickname' => $providerUser->getNickname(),
                'email' => $providerUser->getEmail(),
                'avatar' => $providerUser->getAvatar(),
                'token' => $providerUser->token ?? null,
                'refresh_token' => $providerUser->refreshToken ?? null,
                'token_expires_at' => isset($providerUser->expiresIn)
                    ? now()->addSeconds($providerUser->expiresIn)
                    : null,
            ])->save();

            return $identity;
        });
    }

    /**
     * Encontra a conta pelo e-mail do provedor ou cria uma nova no model
     * configurado — que não precisa ser o App\Models\User.
     *
     * @param  array<string, mixed>  $config
     * @return Model&Authenticatable
     */
    protected function findOrCreateAccount(ProviderUser $providerUser, array $config): Model
    {
        /** @var class-string<Model> $model */
        $model = $config['model'];

        $account = $model::firstOrNew(['email' => $providerUser->getEmail()]);

        if (! $account->exists) {
            $account->fill([
                'name' => $providerUser->getName()
                    ?? $providerUser->getNickname()
                    ?? $providerUser->getEmail(),
            ]);

            /** Provedores OAuth só devolvem contas cujo e-mail já foi verificado. */
            $account->email_verified_at = now();
            $account->save();
        }

        return $account;
    }
}
