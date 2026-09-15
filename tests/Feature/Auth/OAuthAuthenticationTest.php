<?php

namespace Tests\Feature\Auth;

use App\Models\OauthIdentity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as ProviderUser;
use Tests\TestCase;

class OAuthAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_are_redirected_to_the_provider(): void
    {
        Socialite::fake('google');

        $response = $this->get(route('oauth.redirect', 'google'));

        $response->assertRedirect();
    }

    public function test_a_provider_that_is_not_declared_is_not_routable(): void
    {
        $response = $this->get('/auth/facebook/redirect');

        $response->assertNotFound();
    }

    public function test_a_new_account_and_identity_are_created_from_the_callback(): void
    {
        Socialite::fake('google', ProviderUser::fake([
            'id' => 'google-123',
            'name' => 'Flávio Saldanha',
            'email' => 'flavio@example.com',
            'avatar' => 'https://example.com/avatar.jpg',
        ]));

        $response = $this->get(route('oauth.callback', 'google'));

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'flavio@example.com',
            'name' => 'Flávio Saldanha',
        ]);

        $identity = OauthIdentity::sole();

        $this->assertSame('google', $identity->provider);
        $this->assertSame('google-123', $identity->provider_id);
        $this->assertSame('https://example.com/avatar.jpg', $identity->avatar);
        $this->assertTrue($identity->authenticatable->is(User::sole()));
    }

    public function test_an_existing_account_with_the_same_email_is_linked_instead_of_duplicated(): void
    {
        $user = User::factory()->create(['email' => 'flavio@example.com']);

        Socialite::fake('google', ProviderUser::fake([
            'id' => 'google-123',
            'email' => 'flavio@example.com',
        ]));

        $this->get(route('oauth.callback', 'google'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::count());
        $this->assertTrue($user->hasOauthIdentityFor('google'));
    }

    public function test_a_returning_identity_is_matched_by_provider_id_even_when_the_email_changes(): void
    {
        Socialite::fake('google', ProviderUser::fake([
            'id' => 'google-123',
            'email' => 'antigo@example.com',
        ]));
        $this->get(route('oauth.callback', 'google'));
        $this->post('/logout');

        Socialite::fake('google', ProviderUser::fake([
            'id' => 'google-123',
            'email' => 'novo@example.com',
        ]));
        $this->get(route('oauth.callback', 'google'));

        $this->assertAuthenticated();
        $this->assertSame(1, User::count());
        $this->assertSame(1, OauthIdentity::count());
        $this->assertSame('novo@example.com', OauthIdentity::sole()->email);
    }

    public function test_the_same_account_can_hold_identities_from_several_providers(): void
    {
        config()->set('oauth.providers.github', ['label' => 'GitHub']);

        Socialite::fake('google', ProviderUser::fake([
            'id' => 'google-123',
            'email' => 'flavio@example.com',
        ]));
        $this->get(route('oauth.callback', 'google'));
        $this->post('/logout');

        Socialite::fake('github', ProviderUser::fake([
            'id' => 'github-456',
            'email' => 'flavio@example.com',
        ]));
        $this->get('/auth/github/callback');

        $user = User::sole();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(2, $user->oauthIdentities()->count());
        $this->assertTrue($user->hasOauthIdentityFor('google'));
        $this->assertTrue($user->hasOauthIdentityFor('github'));
    }

    public function test_provider_tokens_are_stored_encrypted(): void
    {
        Socialite::fake('google', ProviderUser::fake([
            'id' => 'google-123',
            'email' => 'flavio@example.com',
            'token' => 'token-do-provedor',
        ]));

        $this->get(route('oauth.callback', 'google'));

        $this->assertSame('token-do-provedor', OauthIdentity::sole()->token);
        $this->assertNotSame(
            'token-do-provedor',
            DB::table('oauth_identities')->value('token'),
        );
    }

    public function test_a_denied_authorization_returns_to_the_login_page_with_an_error(): void
    {
        Socialite::fake('google', fn () => throw new InvalidStateException);

        $response = $this->get(route('oauth.callback', 'google'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame(0, User::count());
        $this->assertSame(0, OauthIdentity::count());
    }
}
