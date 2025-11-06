<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        // Seed default categories used by the product form
        if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
            $this->call(\Database\Seeders\CategorySeeder::class);
        }
    }
}
