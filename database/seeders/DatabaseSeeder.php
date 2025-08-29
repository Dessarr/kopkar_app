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
        $this->call([
            memberUser::class,
            AdminUser::class,
            // Add other seeders here if needed
        ]);
    }
}