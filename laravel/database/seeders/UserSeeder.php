<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // The admin account always gets the documented setup-wizard password
        // (Admin@2026!), not the imported hash — a fresh install must be able
        // to log in using the credentials INSTALL.txt promises.
        //
        // The other accounts carry over their bcrypt hashes as-is from a prior
        // install (bcrypt, self-detected by the 'hashed' cast) so those real
        // account holders keep their original passwords.
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@easycyprus.com',
                'account_type' => 'admin',
                'phone' => null,
                'password' => Hash::make('Admin@2026!'),
            ],
            [
                'name' => 'Demo Business Owner',
                'email' => 'business@easycyprus.com',
                'account_type' => 'business',
                'phone' => '+90 533 000 0000',
                'password' => '$2y$12$h3eSZ6FEZqNTsuSJgf4NJ.hsp2varxZWW7QZJuLiHBpPY/QwVJIWS',
            ],
            [
                'name' => 'Sobhan Zaminpeima',
                'email' => 'sobhan.driver@gmail.com',
                'account_type' => 'business',
                'phone' => '5391016485',
                'password' => '$2y$12$zrdjD57Rg2MouMMo1vrkcOEKstZp6FmP3HDpo/eJyhvKfnnDWOEmS',
            ],
            [
                'name' => 'Nebi Işıklar',
                'email' => 'vforneq@gmail.com',
                'account_type' => 'business',
                'phone' => '5338421346',
                'password' => '$2y$12$FdRCU5Gn1HqI4BfBXtcvFuW6Dup2G7W.aZYBxMQNsFrly.TWnhzRG',
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'account_type' => $user['account_type'],
                    'phone' => $user['phone'],
                    'password' => $user['password'],
                    'email_verified_at' => now(),
                ]
            );
        }

        // Keep a known-password demo user for QA / the frontend demo flows.
        User::query()->updateOrCreate(
            ['email' => 'user@easycyprus.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('User@2026!'),
                'account_type' => 'user',
                'email_verified_at' => now(),
            ]
        );
    }
}
