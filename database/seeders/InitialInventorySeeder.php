<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialInventorySeeder extends Seeder
{
    public function run(): void
    {
        $warehouse = DB::table('warehouses')->where('is_default', true)->first();
        if (!$warehouse) {
            return;
        }

        $initialStock = [
            'MP-001' => ['qty' => 25, 'cost' => 18.50],
            'MP-002' => ['qty' => 8, 'cost' => 45.00],
            'MP-003' => ['qty' => 2500, 'cost' => 0.15],
            'MP-004' => ['qty' => 2000, 'cost' => 0.12],
            'MP-005' => ['qty' => 1500, 'cost' => 0.20],
            'MP-006' => ['qty' => 800, 'cost' => 0.08],
            'MP-007' => ['qty' => 600, 'cost' => 0.09],
            'MP-008' => ['qty' => 15, 'cost' => 12.00],
            'MP-009' => ['qty' => 5000, 'cost' => 0.025],
            'MP-010' => ['qty' => 2000, 'cost' => 0.045],
            'MP-011' => ['qty' => 3000, 'cost' => 0.03],
            'MP-012' => ['qty' => 30, 'cost' => 4.50],
            'MP-013' => ['qty' => 15, 'cost' => 5.50],
            'MP-014' => ['qty' => 8, 'cost' => 22.00],
            'MP-015' => ['qty' => 1500, 'cost' => 0.035],
            'ENV-001' => ['qty' => 200, 'cost' => 2.80],
            'ENV-002' => ['qty' => 150, 'cost' => 3.50],
            'ENV-003' => ['qty' => 300, 'cost' => 0.90],
            'ENV-004' => ['qty' => 400, 'cost' => 0.45],
            'ENV-005' => ['qty' => 2000, 'cost' => 0.08],
            'ENV-006' => ['qty' => 1000, 'cost' => 0.25],
            'ENV-007' => ['qty' => 800, 'cost' => 0.15],
            'PT-001' => ['qty' => 45, 'cost' => 8.50],
            'PT-002' => ['qty' => 38, 'cost' => 8.20],
            'PT-003' => ['qty' => 22, 'cost' => 11.80],
            'PT-004' => ['qty' => 60, 'cost' => 3.50],
            'PT-005' => ['qty' => 55, 'cost' => 3.80],
            'PT-006' => ['qty' => 25, 'cost' => 9.20],
            'PT-007' => ['qty' => 18, 'cost' => 14.50],
            'PT-008' => ['qty' => 40, 'cost' => 5.80],
            'PT-009' => ['qty' => 32, 'cost' => 7.20],
            'PT-010' => ['qty' => 28, 'cost' => 6.50],
        ];

        foreach ($initialStock as $sku => $stock) {
            $product = DB::table('products')->where('sku', $sku)->first();
            if (!$product) {
                continue;
            }

            // Crear o actualizar registro de inventario
            $existing = DB::table('inventory')
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->first();

            if (!$existing) {
                DB::table('inventory')->insert([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $stock['qty'],
                    'reserved_quantity' => 0,
                    'average_cost' => $stock['cost'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Registrar movimiento de stock inicial en Kardex
            DB::table('stock_movements')->insert([
                'reference_number' => 'INIT-' . date('Y') . '-' . str_pad($product->id, 5, '0', STR_PAD_LEFT),
                'movement_type' => 'initial_stock',
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $stock['qty'],
                'unit_cost' => $stock['cost'],
                'balance_quantity' => $stock['qty'],
                'balance_average_cost' => $stock['cost'],
                'balance_total_value' => $stock['qty'] * $stock['cost'],
                'notes' => 'Stock inicial del sistema',
                'moved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Actualizar costo del producto
            DB::table('products')->where('id', $product->id)->update([
                'cost' => $stock['cost'],
                'average_cost' => $stock['cost'],
                'updated_at' => now(),
            ]);
        }
    }
}
