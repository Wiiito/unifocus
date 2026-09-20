<?php

namespace Tests\Feature\Console\Commands\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_an_admin_via_command_options(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'Ada Lovelace',
            '--email' => 'ada@unifocus.test',
            '--password' => 'a-strong-password',
        ])->assertSuccessful();

        $admin = Admin::where('email', 'ada@unifocus.test')->firstOrFail();

        $this->assertSame('Ada Lovelace', $admin->name);
        $this->assertTrue(Hash::check('a-strong-password', $admin->password));
    }

    public function test_rejects_an_invalid_email(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'Ada Lovelace',
            '--email' => 'not-an-email',
            '--password' => 'a-strong-password',
        ])->assertFailed();

        $this->assertSame(0, Admin::count());
    }

    public function test_rejects_a_duplicate_email(): void
    {
        Admin::factory()->create(['email' => 'ada@unifocus.test']);

        $this->artisan('admin:create', [
            '--name' => 'Another Admin',
            '--email' => 'ada@unifocus.test',
            '--password' => 'a-strong-password',
        ])->assertFailed();

        $this->assertSame(1, Admin::count());
    }

    public function test_rejects_a_password_shorter_than_eight_characters(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'Ada Lovelace',
            '--email' => 'ada@unifocus.test',
            '--password' => 'short',
        ])->assertFailed();

        $this->assertSame(0, Admin::count());
    }

    public function test_prompts_interactively_when_options_are_omitted(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Name', 'Ada Lovelace')
            ->expectsQuestion('Email', 'ada@unifocus.test')
            ->expectsQuestion('Password', 'a-strong-password')
            ->expectsQuestion('Confirm password', 'a-strong-password')
            ->assertSuccessful();

        $this->assertSame(1, Admin::where('email', 'ada@unifocus.test')->count());
    }

    public function test_fails_when_the_password_confirmation_does_not_match(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Name', 'Ada Lovelace')
            ->expectsQuestion('Email', 'ada@unifocus.test')
            ->expectsQuestion('Password', 'a-strong-password')
            ->expectsQuestion('Confirm password', 'something-else')
            ->assertFailed();

        $this->assertSame(0, Admin::count());
    }
}
