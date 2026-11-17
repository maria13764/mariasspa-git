<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['Maria Spa Admin', 'admin@mariasspa.test', UserRole::Admin],
            ['Spa Manager', 'manager@mariasspa.test', UserRole::Admin],
            ['Spa Coordinator', 'coordinator@mariasspa.test', UserRole::Admin],
            ['Maria Spa Therapist', 'therapist@mariasspa.test', UserRole::Therapist],
            ['Alex Rivera', 'alex.therapist@mariasspa.test', UserRole::Therapist],
            ['Jordan Lee', 'jordan.therapist@mariasspa.test', UserRole::Therapist],
            ['Maria Spa Client', 'client@mariasspa.test', UserRole::Client],
            ['Taylor Morgan', 'taylor.client@mariasspa.test', UserRole::Client],
            ['Casey Brooks', 'casey.client@mariasspa.test', UserRole::Client],
        ];

        foreach ($users as [$name, $email, $role]) {
            User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role' => $role,
                    'email_verified_at' => now(),
                    'password' => 'password',
                ],
            );
        }
    }
}
