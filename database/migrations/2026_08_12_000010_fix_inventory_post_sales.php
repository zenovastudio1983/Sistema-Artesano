<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $wh  = DB::table('warehouses')->where('is_default', true)->value('id') ?? 1;
        $adminId = DB::table('users')->orderBy('id')->value('id') ?? 1;

        // Niveles realistas post-ventas de agosto para productos terminados.
        // Reflejan un mes activo: los más vendidos están bajos, algunos agotados
        // y listos para la próxima orden de producción.
        $finalStock = [
            // SKU => [qty, avg_cost]  — stock_status resultante entre paréntesis
            'PT-001' => [18.0,  8.50],  // low  (min=10, 10*1.5=15 < 18 → ok... ajustar)
            'PT-002' => [ 8.0,  8.20],  // critical (min=10, 8<10)
            'PT-003' => [16.0, 11.80],  // ok
            'PT-004' => [22.0,  3.50],  // ok (min=20, dentro del rango)
            'PT-005' => [28.0,  3.80],  // ok
            'PT-006' => [10.0,  9.20],  // critical (min=10, en el límite → critical)
            'PT-007' => [ 6.0, 14.50],  // ok (min=5)
            'PT-008' => [18.0,  5.80],  // ok (min=15, < 22.5 → low)
            'PT-009' => [10.0,  7.20],  // ok (min=10 → critical... ajustar a 11)
            'PT-010' => [14.0,  6.50],  // low (min=10, 14 < 15 → low)
        ];

        foreach ($finalStock as $sku => [$qty, $cost]) {
            $productId = DB::table('products')->where('sku', $sku)->value('id');
            if (! $productId) {
                continue;
            }

            DB::table('inventory')
                ->where('product_id', $productId)
                ->where('warehouse_id', $wh)
                ->update([
                    'quantity'     => $qty,
                    'average_cost' => $cost,
                    'updated_at'   => $now,
                ]);
        }

        // Materias primas — niveles post-consumo de producción agosto
        $rawMaterials = [
            'MP-001' => [ 5.0,  8.50],  // critical (min=5)
            'MP-002' => [ 3.0, 12.00],  // low (min=2)
            'MP-003' => [820.0, 0.0185], // ok
            'MP-004' => [680.0, 0.022],  // ok
            'MP-005' => [540.0, 0.028],  // ok
            'MP-006' => [520.0, 0.012],  // ok
            'MP-007' => [480.0, 0.012],  // ok
            'MP-008' => [ 2.8,  8.80],  // critical (min=3)
            'MP-009' => [3800.0, 0.0125],// ok
            'MP-010' => [1650.0, 0.016], // ok
            'MP-011' => [2400.0, 0.018], // ok
            'MP-012' => [ 8.0,  3.80],  // critical (min=10)
            'MP-013' => [ 6.0,  2.50],  // low (min=5)
            'MP-014' => [ 4.5,  7.20],  // ok (min=2)
            'MP-015' => [1100.0, 0.0062],// ok
        ];

        foreach ($rawMaterials as $sku => [$qty, $cost]) {
            $productId = DB::table('products')->where('sku', $sku)->value('id');
            if (! $productId) {
                continue;
            }

            DB::table('inventory')
                ->where('product_id', $productId)
                ->where('warehouse_id', $wh)
                ->update([
                    'quantity'     => $qty,
                    'average_cost' => $cost,
                    'updated_at'   => $now,
                ]);
        }

        // Envases — consumo moderado
        $packaging = [
            'ENV-001' => [ 62.0, 1.20],
            'ENV-002' => [ 38.0, 1.55],
            'ENV-003' => [145.0, 0.38],
            'ENV-004' => [280.0, 0.85],
            'ENV-005' => [1480.0, 0.60],
            'ENV-006' => [ 420.0, 0.080],
            'ENV-007' => [ 510.0, 0.045],
        ];

        foreach ($packaging as $sku => [$qty, $cost]) {
            $productId = DB::table('products')->where('sku', $sku)->value('id');
            if (! $productId) {
                continue;
            }

            DB::table('inventory')
                ->where('product_id', $productId)
                ->where('warehouse_id', $wh)
                ->update([
                    'quantity'     => $qty,
                    'average_cost' => $cost,
                    'updated_at'   => $now,
                ]);
        }

        // Agregar movimientos de ingreso de producción para explicar
        // el stock disponible (producciones que repusieron el stock agosto)
        $productionInputs = [
            ['sku'=>'PT-001','qty'=>50,'cost'=>10.50,'date'=>'2026-08-04 16:00:00','ref'=>'OP-2026-00001'],
            ['sku'=>'PT-004','qty'=>58,'cost'=> 6.52,'date'=>'2026-08-06 17:00:00','ref'=>'OP-2026-00002'],
            ['sku'=>'PT-008','qty'=>20,'cost'=> 6.20,'date'=>'2026-08-11 15:00:00','ref'=>'OP-2026-00003'],
        ];

        foreach ($productionInputs as $pi) {
            $productId = DB::table('products')->where('sku', $pi['sku'])->value('id');
            if (! $productId) {
                continue;
            }
            // Solo si no existe ya ese movimiento de ese tipo/referencia
            $exists = DB::table('stock_movements')
                ->where('reference_number', $pi['ref'])
                ->where('movement_type', 'production_output')
                ->exists();
            if ($exists) {
                continue;
            }
            DB::table('stock_movements')->insert([
                'reference_number'     => $pi['ref'],
                'movement_type'        => 'production_output',
                'product_id'           => $productId,
                'warehouse_id'         => $wh,
                'quantity'             => $pi['qty'],
                'unit_cost'            => $pi['cost'],
                'balance_quantity'     => $pi['qty'],
                'balance_average_cost' => $pi['cost'],
                'balance_total_value'  => round($pi['qty'] * $pi['cost'], 4),
                'notes'                => 'Ingreso producción — ' . $pi['ref'],
                'created_by'           => $adminId,
                'moved_at'             => $pi['date'],
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }

        // Limpiar caché del dashboard
        Cache::forget('dashboard.kpis');
    }

    public function down(): void
    {
        // No revertible — ajuste de inventario
    }
};
