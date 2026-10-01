<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.test');
        $password = env('ADMIN_PASSWORD');

        if (blank($password) && app()->environment('production')) {
            throw new \RuntimeException('ADMIN_PASSWORD must be set before seeding production.');
        }

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'HomeCyp Admin'),
                'password' => Hash::make($password ?: 'password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('super_admin');
    }
}
