<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = env('INITIAL_ADMIN_EMAIL');
        $password = env('INITIAL_ADMIN_PASSWORD');

        if (blank($email) || blank($password)) {
            $this->command?->warn(
                'INITIAL_ADMIN_EMAIL / INITIAL_ADMIN_PASSWORD belum diisi di .env, akun admin tidak dibuat.'
            );

            return;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => env('INITIAL_ADMIN_NAME', 'Administrator'),
                'password' => $password,
                'role' => 'admin',
            ],
        );

        $this->command?->info(
            $user->wasRecentlyCreated
                ? "Akun admin dibuat: {$email}"
                : "Akun {$email} sudah ada, tidak diubah."
        );
    }
}