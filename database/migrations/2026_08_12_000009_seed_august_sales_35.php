<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Omitir si ya existen estas ventas
        if (DB::table('sales')->where('order_number', 'VTA-2026-100')->exists()) {
            return;
        }

        $now     = now();
        $adminId = DB::table('users')->orderBy('id')->value('id') ?? 1;
        $wh      = DB::table('warehouses')->where('is_default', true)->value('id') ?? 1;

        // Catálogo: precio y costo por SKU
        $catalog = DB::table('products')
            ->where('type', 'finished_product')
            ->where('status', 'active')
            ->get()
            ->keyBy('sku');

        $customers = DB::table('customers')->pluck('id', 'code');

        // ── 35 ventas distribuidas en agosto 2026 ────────────────────────────
        // Formato: [order_number, sale_date, customer_code, status, [[sku, qty], ...]]
        $salesDef = [
            // Día 1
            ['VTA-2026-100','2026-08-01','CLI-001','paid',     [['PT-001',8],['PT-004',12],['PT-006',4]]],
            ['VTA-2026-101','2026-08-01','CLI-004','paid',     [['PT-009',2],['PT-010',1]]],
            ['VTA-2026-102','2026-08-01','CLI-006','confirmed',[['PT-004',5],['PT-005',3]]],
            // Día 2
            ['VTA-2026-103','2026-08-02','CLI-002','invoiced', [['PT-007',4],['PT-001',6]]],
            ['VTA-2026-104','2026-08-02','CLI-003','paid',     [['PT-008',6],['PT-009',4]]],
            ['VTA-2026-105','2026-08-02','CLI-007','confirmed',[['PT-001',5],['PT-002',4]]],
            // Día 3
            ['VTA-2026-106','2026-08-03','CLI-005','paid',     [['PT-007',8],['PT-006',5],['PT-010',6]]],
            ['VTA-2026-107','2026-08-03','CLI-001','invoiced', [['PT-004',15],['PT-005',10]]],
            ['VTA-2026-108','2026-08-03','CLI-008','paid',     [['PT-003',2],['PT-009',2]]],
            // Día 4
            ['VTA-2026-109','2026-08-04','CLI-003','paid',     [['PT-004',6],['PT-008',4]]],
            ['VTA-2026-110','2026-08-04','CLI-006','invoiced', [['PT-010',3],['PT-006',2]]],
            ['VTA-2026-111','2026-08-04','CLI-002','confirmed',[['PT-001',8],['PT-003',4]]],
            // Día 5
            ['VTA-2026-112','2026-08-05','CLI-007','paid',     [['PT-007',5],['PT-001',8],['PT-010',4]]],
            ['VTA-2026-113','2026-08-05','CLI-004','confirmed',[['PT-009',3],['PT-004',4]]],
            ['VTA-2026-114','2026-08-05','CLI-001','invoiced', [['PT-002',10],['PT-006',6]]],
            // Día 6
            ['VTA-2026-115','2026-08-06','CLI-003','paid',     [['PT-005',8],['PT-004',10]]],
            ['VTA-2026-116','2026-08-06','CLI-008','paid',     [['PT-001',3],['PT-003',1]]],
            ['VTA-2026-117','2026-08-06','CLI-005','invoiced', [['PT-007',6],['PT-010',8],['PT-009',5]]],
            // Día 7
            ['VTA-2026-118','2026-08-07','CLI-002','paid',     [['PT-004',12],['PT-005',8],['PT-008',6]]],
            ['VTA-2026-119','2026-08-07','CLI-006','confirmed',[['PT-006',3],['PT-010',4]]],
            ['VTA-2026-120','2026-08-07','CLI-001','paid',     [['PT-001',10],['PT-002',8],['PT-007',3]]],
            // Día 8
            ['VTA-2026-121','2026-08-08','CLI-004','paid',     [['PT-004',3],['PT-009',2]]],
            ['VTA-2026-122','2026-08-08','CLI-007','invoiced', [['PT-003',5],['PT-006',4],['PT-010',3]]],
            ['VTA-2026-123','2026-08-08','CLI-003','confirmed',[['PT-008',8],['PT-009',6]]],
            // Día 9
            ['VTA-2026-124','2026-08-09','CLI-005','paid',     [['PT-007',10],['PT-006',6],['PT-001',5]]],
            ['VTA-2026-125','2026-08-09','CLI-001','invoiced', [['PT-004',20],['PT-005',15]]],
            ['VTA-2026-126','2026-08-09','CLI-008','confirmed',[['PT-002',3],['PT-010',2]]],
            // Día 10
            ['VTA-2026-127','2026-08-10','CLI-002','paid',     [['PT-007',5],['PT-003',3],['PT-009',4]]],
            ['VTA-2026-128','2026-08-10','CLI-006','invoiced', [['PT-004',8],['PT-005',6]]],
            ['VTA-2026-129','2026-08-10','CLI-004','confirmed',[['PT-001',4],['PT-008',3]]],
            // Día 11
            ['VTA-2026-130','2026-08-11','CLI-001','paid',     [['PT-001',12],['PT-002',10],['PT-006',5]]],
            ['VTA-2026-131','2026-08-11','CLI-007','confirmed',[['PT-007',4],['PT-010',5]]],
            ['VTA-2026-132','2026-08-11','CLI-003','invoiced', [['PT-004',10],['PT-009',6]]],
            // Día 12
            ['VTA-2026-133','2026-08-12','CLI-002','confirmed',[['PT-003',3],['PT-005',4]]],
            ['VTA-2026-134','2026-08-12','CLI-005','quotation',[['PT-007',10],['PT-001',8],['PT-010',6]]],
        ];

        $paymentMethods = ['transfer','transfer','transfer','cash','check'];

        foreach ($salesDef as [$orderNum, $saleDate, $custCode, $status, $lines]) {
            // Calcular totales desde el catálogo
            $subtotal = 0.0;
            $cog      = 0.0;
            $lineData = [];

            foreach ($lines as [$sku, $qty]) {
                $p = $catalog[$sku] ?? null;
                if (! $p) {
                    continue;
                }
                $price    = (float) $p->price;
                $cost     = (float) $p->cost;
                $sub      = round($price * $qty, 4);
                $cogLine  = round($cost  * $qty, 4);
                $subtotal += $sub;
                $cog      += $cogLine;
                $lineData[] = [
                    'product_id'      => (int) $p->id,
                    'description'     => $p->name,
                    'quantity'        => $qty,
                    'unit'            => 'und',
                    'unit_price'      => $price,
                    'unit_cost'       => $cost,
                    'discount_percent'=> 0,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }

            if (empty($lineData)) {
                continue;
            }

            $taxRate   = 21.00;
            $taxAmount = round($subtotal * $taxRate / 100, 4);
            $total     = round($subtotal + $taxAmount, 4);
            $customerId = $customers[$custCode] ?? 1;

            $saleId = DB::table('sales')->insertGetId([
                'order_number'    => $orderNum,
                'status'          => $status,
                'type'            => 'sale',
                'customer_id'     => $customerId,
                'warehouse_id'    => $wh,
                'sale_date'       => $saleDate,
                'due_date'        => date('Y-m-d', strtotime($saleDate . ' +30 days')),
                'subtotal'        => $subtotal,
                'discount_percent'=> 0,
                'discount_amount' => 0,
                'tax_rate'        => $taxRate,
                'tax_amount'      => $taxAmount,
                'total'           => $total,
                'cost_of_goods'   => $cog,
                'currency'        => 'ARS',
                'exchange_rate'   => 1,
                'payment_method'  => 'transfer',
                'seller_id'       => $adminId,
                'created_by'      => $adminId,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            // Ítems de la venta
            foreach ($lineData as $li) {
                DB::table('sale_items')->insert(array_merge($li, ['sale_id' => $saleId]));
            }

            // Pago para ventas pagadas
            if ($status === 'paid') {
                DB::table('sale_payments')->insert([
                    'sale_id'      => $saleId,
                    'amount'       => $total,
                    'method'       => $paymentMethods[array_rand($paymentMethods)],
                    'payment_date' => date('Y-m-d', strtotime($saleDate . ' +' . rand(1,3) . ' days')),
                    'reference'    => 'TRF-' . rand(100000, 999999),
                    'notes'        => 'Pago recibido conforme',
                    'created_by'   => $adminId,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
            }

            // Movimiento de stock de salida (solo el registro, sin modificar inventory)
            if (in_array($status, ['confirmed', 'invoiced', 'paid'])) {
                foreach ($lines as [$sku, $qty]) {
                    $p = $catalog[$sku] ?? null;
                    if (! $p) {
                        continue;
                    }
                    $avgCost = (float) $p->cost;
                    DB::table('stock_movements')->insert([
                        'reference_number'     => $orderNum,
                        'movement_type'        => 'sale_out',
                        'product_id'           => (int) $p->id,
                        'warehouse_id'         => $wh,
                        'quantity'             => -$qty,
                        'unit_cost'            => $avgCost,
                        'balance_quantity'     => 0,
                        'balance_average_cost' => $avgCost,
                        'balance_total_value'  => 0,
                        'moveable_type'        => 'App\\Models\\Sale',
                        'moveable_id'          => $saleId,
                        'notes'                => "Salida por venta {$orderNum}",
                        'created_by'           => $adminId,
                        'moved_at'             => $saleDate . ' ' . sprintf('%02d:%02d:00', rand(8,17), rand(0,59)),
                        'created_at'           => $now,
                        'updated_at'           => $now,
                    ]);
                }
            }
        }

        // Limpiar caché de KPIs para que el dashboard refleje los nuevos datos
        \Illuminate\Support\Facades\Cache::forget('dashboard.kpis');
    }

    public function down(): void
    {
        $orderNumbers = array_map(
            fn($n) => 'VTA-2026-' . str_pad($n, 3, '0', STR_PAD_LEFT),
            range(100, 134)
        );

        $saleIds = DB::table('sales')
            ->whereIn('order_number', $orderNumbers)
            ->pluck('id');

        DB::table('sale_payments')->whereIn('sale_id', $saleIds)->delete();
        DB::table('sale_items')->whereIn('sale_id', $saleIds)->delete();
        DB::table('stock_movements')->whereIn('reference_number', $orderNumbers)->delete();
        DB::table('sales')->whereIn('order_number', $orderNumbers)->delete();
    }
};
