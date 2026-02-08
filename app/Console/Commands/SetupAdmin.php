<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SetupAdmin extends Command
{
    protected $signature = 'app:setup-admin
                            {--name= : Admin name}
                            {--email= : Admin email}
                            {--phone= : Admin phone number}
                            {--password= : Admin password}
                            {--force : Skip confirmation}';

    protected $description = 'Create or update the first admin user for the system';

    public function handle(): int
    {
        $this->components->info('Setup Admin Account');

        // Check if admin already exists
        $existingAdmin = User::where('role', UserRole::SuperAdmin)->first();

        if ($existingAdmin && ! $this->option('force')) {
            $this->components->warn("Super Admin already exists: {$existingAdmin->email}");

            if (! $this->confirm('Do you want to create another admin?', false)) {
                $this->components->info('Setup cancelled.');

                return self::SUCCESS;
            }
        }

        // Gather info
        $name = $this->option('name') ?: $this->ask('Admin name', 'Administrator');
        $email = $this->option('email') ?: $this->ask('Admin email');
        $phone = $this->option('phone') ?: $this->ask('Admin phone (optional)', '');
        $password = $this->option('password') ?: $this->secret('Admin password (min 8 chars)');

        // Validate
        if (empty($email)) {
            $this->components->error('Email is required.');

            return self::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->components->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        // Check email uniqueness
        $existing = User::where('email', $email)->first();

        if ($existing) {
            if (! $this->option('force') && ! $this->confirm("User with email {$email} already exists. Update to Super Admin?", true)) {
                return self::SUCCESS;
            }

            $existing->update([
                'name' => $name,
                'password' => Hash::make($password),
                'role' => UserRole::SuperAdmin,
                'status' => UserStatus::Active,
                'phone' => $phone ?: $existing->phone,
            ]);

            $this->components->info("Updated {$email} to Super Admin.");

            return self::SUCCESS;
        }

        // Create new admin
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone ?: null,
            'password' => Hash::make($password),
            'role' => UserRole::SuperAdmin,
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
            'referral_code' => strtoupper(Str::random(8)),
        ]);

        $this->newLine();
        $this->components->info('Super Admin created successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $user->name],
                ['Email', $user->email],
                ['Phone', $user->phone ?? '-'],
                ['Role', 'Super Admin'],
            ]
        );

        return self::SUCCESS;
    }
}
