<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::firstOrCreate(
        ['email' => env('ADMIN_EMAIL', 'admin@admin.com')],
        [
            'name' => 'Admin',
            'password' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe123!')),
            'email_verified_at' => now(),
        ]);

        $this->call([
            CourtSeeder::class ,
            AdminUserSeeder::class ,
        ]);
    }
}
