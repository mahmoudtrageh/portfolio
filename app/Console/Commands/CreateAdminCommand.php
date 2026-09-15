<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Creates (or re-passwords) the single dashboard account. There is no public
 * registration route, so this is the only way an account comes into existence.
 */
final class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create
                            {--email= : The account email}
                            {--name= : The display name}';

    protected $description = 'Create the dashboard admin account, or reset its password';

    public function handle(): int
    {
        $email = $this->option('email') ?: text(label: 'Email', required: true);

        $existing = User::query()->where('email', $email)->first();

        $name = $this->option('name')
            ?: $existing?->name
            ?: text(label: 'Name', required: true);

        $password = password(label: 'Password', required: true);

        if ($password !== password(label: 'Confirm password', required: true)) {
            $this->error('The passwords do not match.');

            return self::FAILURE;
        }

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password],
            [
                'email' => ['required', 'email', 'max:190'],
                'name' => ['required', 'string', 'max:120'],
                'password' => ['required', Password::default()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password)],
        );

        $this->info($existing
            ? "Password reset for {$user->email}."
            : "Admin account created for {$user->email}.");

        return self::SUCCESS;
    }
}
