<?php

namespace Tests\Feature\Console\Commands\Notifications;

use App\Models\User;
use App\Notifications\StreakAtRisk;
use App\Notifications\TestNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendTestNotificationCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_sends_a_test_notification_to_the_student_found_by_email(): void
    {
        $user = User::factory()->create(['email' => 'ana@unifocus.test']);
        Notification::fake();

        $this->artisan('notifications:test', ['user' => 'ana@unifocus.test'])->assertSuccessful();

        Notification::assertSentTo($user, TestNotification::class);
    }

    public function test_sends_the_chosen_type_to_the_student_found_by_id(): void
    {
        $user = User::factory()->create();
        Notification::fake();

        $this->artisan('notifications:test', ['user' => $user->id, '--type' => 'foguinho'])->assertSuccessful();

        Notification::assertSentTo($user, StreakAtRisk::class);
    }

    public function test_fails_for_unknown_student_or_type(): void
    {
        $user = User::factory()->create();
        Notification::fake();

        $this->artisan('notifications:test', ['user' => 'ninguem@unifocus.test'])
            ->expectsOutputToContain('Estudante não encontrado.')
            ->assertFailed();
        $this->artisan('notifications:test', ['user' => $user->id, '--type' => 'spam'])
            ->expectsOutputToContain('Tipo inválido.')
            ->assertFailed();

        Notification::assertNothingSent();
    }
}
