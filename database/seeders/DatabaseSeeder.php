<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WarehouseSeeder::class,
            CategorySeeder::class,
            UserRolePermissionSeeder::class,
            ProductSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
            RecipeSeeder::class,
            InitialInventorySeeder::class,
        ]);
    }
}
