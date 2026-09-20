<?php

namespace App\Console\Commands\Admin;

use App\Models\Admin;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('admin:create {--name=} {--email=} {--password=}')]
#[Description('Create a new admin account')]
class CreateAdminCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?? text(label: 'Name', required: true);

        $email = $this->option('email') ?? text(label: 'Email', required: true);

        $providedPassword = $this->option('password');

        $password = $providedPassword ?? password(label: 'Password', required: true);

        if (! $providedPassword && $password !== password(label: 'Confirm password', required: true)) {
            $this->components->error('The password confirmation does not match.');

            return self::FAILURE;
        }

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:160'],
                'email' => ['required', 'string', 'email', 'max:180', Rule::unique('admins', 'email')],
                'password' => ['required', 'string', Password::min(8)],
            ]
        );

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first());

            return self::FAILURE;
        }

        $admin = Admin::create($validator->validated());

        $this->components->info("Admin \"{$admin->name}\" <{$admin->email}> created successfully.");

        return self::SUCCESS;
    }
}
