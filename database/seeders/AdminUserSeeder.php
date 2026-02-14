<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
        ['email' => env('ADMIN_EMAIL', 'admin@admin.com')],
        [
            'name' => 'Admin',
            'password' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe123!')),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]
        );

        // If user already exists, ensure role is admin
        if ($user->role !== 'admin') {
            $user->update(['role' => 'admin']);
        }
    }
}
