<?php

namespace Database\Seeders;

use App\Models\Court;
use Illuminate\Database\Seeder;

class CourtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            Court::create([
                'name' => 'Court ' . $i,
                'price_per_hour' => 210, // Standard price
                'is_active' => true,
            ]);
        }
    }
}
