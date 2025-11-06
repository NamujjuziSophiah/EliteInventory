<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $defaults = [
            ['name' => 'Beverages', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Groceries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Electronics', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Household', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stationery', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($defaults as $d) {
            DB::table('categories')->updateOrInsert(['name' => $d['name']], $d);
        }
    }
}
