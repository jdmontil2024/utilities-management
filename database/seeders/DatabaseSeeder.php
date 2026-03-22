<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run user seeder first
        $this->call([
            UserSeeder::class,
            // Add other seeders here
            // BuildingSeeder::class,
            // TenantSeeder::class,
            // etc.
        ]);
    }
}