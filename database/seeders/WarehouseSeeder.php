<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'code' => 'ALM-001',
                'name' => 'Almacén Principal',
                'description' => 'Almacén principal de materias primas y productos terminados',
                'address' => 'Av. Artesanal 123',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'code' => 'ALM-002',
                'name' => 'Almacén de Producción',
                'description' => 'Almacén de materiales en proceso y semi-terminados',
                'address' => 'Taller de Producción',
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'code' => 'ALM-003',
                'name' => 'Almacén de Productos Terminados',
                'description' => 'Almacén exclusivo para productos terminados listos para la venta',
                'address' => 'Zona de Despacho',
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $warehouse) {
            DB::table('warehouses')->insertOrIgnore(array_merge($warehouse, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
