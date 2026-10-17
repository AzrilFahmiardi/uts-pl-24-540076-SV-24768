<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            ['name' => 'Admin Lab', 'password' => env('ADMIN_PASSWORD', 'password')]
        );

        if (Equipment::count() === 0) {
            Equipment::create(['name' => 'Oscilloscope', 'code' => 'ALT-0001', 'category' => 'Elektronika', 'condition' => 'baik', 'quantity' => 3]);
            Equipment::create(['name' => 'Multimeter Digital', 'code' => 'ALT-0002', 'category' => 'Ukur', 'condition' => 'baik', 'quantity' => 10]);
        }
    }
}
