<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Solo ejecutar si no hay ventas (evitar duplicados en re-deploy)
        if (DB::table('sales')->count() > 0) {
            return;
        }

        $warehouseId = DB::table('warehouses')->where('is_default', true)->value('id') ?? 1;
        $adminId     = DB::table('users')->orderBy('id')->value('id') ?? 1;
        $now         = now();

        // Clientes por código
        $customers = DB::table('customers')->pluck('id', 'code');

        // Productos por SKU
        $products = DB::table('products')
            ->where('type', 'finished_product')
            ->where('status', 'active')
            ->get()
            ->keyBy('sku');

        // Helper: insertar venta completa
        $insertSale = function (
            string $orderNumber,
            string $date,
            string $status,
            string $customerCode,
            array  $items        // [['sku' => 'PT-001', 'qty' => 2], ...]
        ) use ($warehouseId, $adminId, $customers, $products, $now): void {

            $customerId  = $customers[$customerCode] ?? 1;

            $subtotal = 0;
            $cogTotal = 0;
            $lineItems = [];

            foreach ($items as $item) {
                $p = $products[$item['sku']] ?? null;
                if (! $p) {
                    continue;
                }
                $qty      = $item['qty'];
                $price    = (float) $p->price;
                $cost     = (float) $p->cost;
                $sub      = round($price * $qty, 4);
                $cog      = round($cost  * $qty, 4);
                $margin   = round($sub - $cog, 4);

                $subtotal += $sub;
                $cogTotal += $cog;

                $lineItems[] = [
                    'name'  => $p->name,
                    'price' => $price,
                    'cost'  => $cost,
                    'qty'   => $qty,
                    'pid'   => $p->id,
                ];
            }

            $taxRate    = 21.00;
            $taxAmount  = round($subtotal * $taxRate / 100, 4);
            $total      = round($subtotal + $taxAmount, 4);

            $saleId = DB::table('sales')->insertGetId([
                'order_number'    => $orderNumber,
                'status'          => $status,
                'type'            => 'sale',
                'customer_id'     => $customerId,
                'warehouse_id'    => $warehouseId,
                'sale_date'       => $date,
                'due_date'        => date('Y-m-d', strtotime($date . ' +30 days')),
                'subtotal'        => $subtotal,
                'discount_percent'=> 0,
                'discount_amount' => 0,
                'tax_rate'        => $taxRate,
                'tax_amount'      => $taxAmount,
                'total'           => $total,
                'cost_of_goods'   => $cogTotal,
                'currency'        => 'ARS',
                'exchange_rate'   => 1,
                'payment_method'  => 'transfer',
                'seller_id'       => $adminId,
                'created_by'      => $adminId,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            $saleItemRows = [];
            foreach ($lineItems as $li) {
                $saleItemRows[] = [
                    'sale_id'         => $saleId,
                    'product_id'      => $li['pid'],
                    'description'     => $li['name'],
                    'quantity'        => $li['qty'],
                    'unit'            => 'und',
                    'unit_price'      => $li['price'],
                    'unit_cost'       => $li['cost'],
                    'discount_percent'=> 0,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }

            if ($saleItemRows) {
                DB::table('sale_items')->insert($saleItemRows);
            }
        };

        // ── Junio 2026 ──────────────────────────────────────────────────────
        $insertSale('VTA-2026-001', '2026-06-05', 'paid', 'CLI-001', [
            ['sku' => 'PT-001', 'qty' => 5],
            ['sku' => 'PT-004', 'qty' => 10],
            ['sku' => 'PT-006', 'qty' => 3],
        ]);

        $insertSale('VTA-2026-002', '2026-06-14', 'invoiced', 'CLI-002', [
            ['sku' => 'PT-007', 'qty' => 4],
            ['sku' => 'PT-002', 'qty' => 6],
        ]);

        $insertSale('VTA-2026-003', '2026-06-22', 'paid', 'CLI-005', [
            ['sku' => 'PT-003', 'qty' => 3],
            ['sku' => 'PT-010', 'qty' => 5],
            ['sku' => 'PT-009', 'qty' => 8],
        ]);

        // ── Julio 2026 ──────────────────────────────────────────────────────
        $insertSale('VTA-2026-004', '2026-07-03', 'paid', 'CLI-003', [
            ['sku' => 'PT-004', 'qty' => 15],
            ['sku' => 'PT-005', 'qty' => 10],
            ['sku' => 'PT-008', 'qty' => 12],
        ]);

        $insertSale('VTA-2026-005', '2026-07-12', 'invoiced', 'CLI-001', [
            ['sku' => 'PT-001', 'qty' => 8],
            ['sku' => 'PT-006', 'qty' => 4],
            ['sku' => 'PT-007', 'qty' => 3],
        ]);

        $insertSale('VTA-2026-006', '2026-07-20', 'paid', 'CLI-005', [
            ['sku' => 'PT-002', 'qty' => 6],
            ['sku' => 'PT-003', 'qty' => 4],
            ['sku' => 'PT-010', 'qty' => 7],
        ]);

        $insertSale('VTA-2026-007', '2026-07-28', 'confirmed', 'CLI-002', [
            ['sku' => 'PT-007', 'qty' => 5],
            ['sku' => 'PT-009', 'qty' => 6],
        ]);

        // ── Agosto 2026 ─────────────────────────────────────────────────────
        $insertSale('VTA-2026-008', '2026-08-02', 'paid', 'CLI-001', [
            ['sku' => 'PT-001', 'qty' => 10],
            ['sku' => 'PT-004', 'qty' => 8],
            ['sku' => 'PT-006', 'qty' => 5],
        ]);

        $insertSale('VTA-2026-009', '2026-08-07', 'invoiced', 'CLI-005', [
            ['sku' => 'PT-007', 'qty' => 6],
            ['sku' => 'PT-003', 'qty' => 4],
            ['sku' => 'PT-010', 'qty' => 5],
        ]);

        $insertSale('VTA-2026-010', '2026-08-10', 'confirmed', 'CLI-002', [
            ['sku' => 'PT-002', 'qty' => 8],
            ['sku' => 'PT-005', 'qty' => 6],
            ['sku' => 'PT-009', 'qty' => 4],
        ]);
    }

    public function down(): void
    {
        DB::table('sales')
            ->whereIn('order_number', [
                'VTA-2026-001', 'VTA-2026-002', 'VTA-2026-003',
                'VTA-2026-004', 'VTA-2026-005', 'VTA-2026-006', 'VTA-2026-007',
                'VTA-2026-008', 'VTA-2026-009', 'VTA-2026-010',
            ])
            ->delete();
    }
};
