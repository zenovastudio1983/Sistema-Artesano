<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Omitir si ya hay ítems de OC (datos ya sembrados)
        if (DB::table('purchase_order_items')->count() > 0) {
            return;
        }

        $now = now();
        $adminId     = DB::table('users')->orderBy('id')->value('id') ?? 1;
        $warehouseId = DB::table('warehouses')->where('is_default', true)->value('id') ?? 1;

        // ── Lookups por clave de negocio ────────────────────────────────────
        $prods     = DB::table('products')->where('status', 'active')->get()->keyBy('sku');
        $suppliers = DB::table('suppliers')->pluck('id', 'code');
        $customers = DB::table('customers')->pluck('id', 'code');

        $pid = fn(string $sku): int => (int) ($prods[$sku]->id ?? 0);

        // ────────────────────────────────────────────────────────────────────
        // 1. CLIENTES ADICIONALES
        // ────────────────────────────────────────────────────────────────────
        $extraCustomers = [
            ['code'=>'CLI-006','business_name'=>'Herboristería San Benito','trade_name'=>'San Benito',
             'tax_id'=>'20661122334','customer_type'=>'retail','email'=>'pedidos@sanbenito.com.ar',
             'phone'=>'0341-450-6601','payment_days'=>0,'discount_percent'=>0,'credit_limit'=>0],
            ['code'=>'CLI-007','business_name'=>'Lavanda & Co. Diseño','trade_name'=>'Lavanda & Co',
             'tax_id'=>'20772233445','customer_type'=>'wholesale','email'=>'compras@lavandaco.com.ar',
             'phone'=>'011-4456-7702','payment_days'=>30,'discount_percent'=>5,'credit_limit'=>2000],
            ['code'=>'CLI-008','business_name'=>'Fernández Rodrigo Omar','trade_name'=>null,
             'tax_id'=>'20883344556','customer_type'=>'retail','email'=>'rodrigo.fernandez@gmail.com',
             'phone'=>'15-5523-8803','payment_days'=>0,'discount_percent'=>0,'credit_limit'=>0],
        ];
        foreach ($extraCustomers as $c) {
            DB::table('customers')->insertOrIgnore(array_merge($c, [
                'price_list'=>'regular','is_active'=>true,'created_at'=>$now,'updated_at'=>$now,
            ]));
        }

        // ── Reload customers ────────────────────────────────────────────────
        $customers = DB::table('customers')->pluck('id', 'code');

        // ────────────────────────────────────────────────────────────────────
        // 2. PROVEEDORES ADICIONALES
        // ────────────────────────────────────────────────────────────────────
        $extraSuppliers = [
            ['code'=>'PROV-006','business_name'=>'Colores Naturales del Sur SRL','trade_name'=>'ColoresNat',
             'tax_id'=>'30661122339','email'=>'ventas@coloresnat.com.ar','phone'=>'0351-460-1201',
             'contact_name'=>'Silvia Romero','payment_days'=>30,'credit_limit'=>3000,'rating'=>4.2,
             'city'=>'Córdoba'],
            ['code'=>'PROV-007','business_name'=>'Packaging Premium SA','trade_name'=>'PackPremium',
             'tax_id'=>'30772233448','email'=>'info@packpremium.com.ar','phone'=>'011-4870-0077',
             'contact_name'=>'Marcos Vidal','payment_days'=>15,'credit_limit'=>5000,'rating'=>4.5,
             'city'=>'Buenos Aires'],
        ];
        foreach ($extraSuppliers as $s) {
            DB::table('suppliers')->insertOrIgnore(array_merge($s, [
                'tax_type'=>'CUIT','currency'=>'ARS','current_balance'=>0,
                'is_active'=>true,'created_at'=>$now,'updated_at'=>$now,
            ]));
        }

        // ── Reload suppliers ────────────────────────────────────────────────
        $suppliers = DB::table('suppliers')->pluck('id', 'code');

        // ────────────────────────────────────────────────────────────────────
        // 3. ÓRDENES DE COMPRA + ÍTEMS + RECEPCIONES
        // ────────────────────────────────────────────────────────────────────
        $purchaseOrders = [
            // Mayo 2026 (histórico)
            [
                'order_number'=>'OC-2026-000', 'order_date'=>'2026-05-15',
                'status'=>'received','received_date'=>'2026-05-22',
                'supplier_code'=>'PROV-001',
                'subtotal'=>870.00,'tax_rate'=>21,'tax_amount'=>182.70,'total'=>1052.70,
                'payment_terms'=>'30 días','notes'=>'Reposición trimestral de ceras',
                'items'=>[
                    ['sku'=>'MP-001','qty'=>60,'price'=>8.50,'unit'=>'kg','recv'=>60],
                    ['sku'=>'MP-002','qty'=>25,'price'=>12.00,'unit'=>'kg','recv'=>25],
                    ['sku'=>'ENV-005','qty'=>500,'price'=>0.60,'unit'=>'m','recv'=>500],
                ],
            ],
            // Julio 2026 (OC-2026-001 existente en local — insertOrIgnore)
            [
                'order_number'=>'OC-2026-001','order_date'=>'2026-07-08',
                'status'=>'received','received_date'=>'2026-07-16',
                'supplier_code'=>'PROV-001',
                'subtotal'=>870.00,'tax_rate'=>21,'tax_amount'=>182.70,'total'=>1052.70,
                'payment_terms'=>'30 días','notes'=>'Reposición ceras y envases vela',
                'items'=>[
                    ['sku'=>'MP-001','qty'=>50,'price'=>8.50,'unit'=>'kg','recv'=>50],
                    ['sku'=>'MP-002','qty'=>20,'price'=>12.00,'unit'=>'kg','recv'=>20],
                    ['sku'=>'ENV-001','qty'=>300,'price'=>1.20,'unit'=>'und','recv'=>300],
                ],
            ],
            [
                'order_number'=>'OC-2026-002','order_date'=>'2026-07-20',
                'status'=>'received','received_date'=>'2026-07-28',
                'supplier_code'=>'PROV-002',
                'subtotal'=>332.40,'tax_rate'=>21,'tax_amount'=>69.80,'total'=>402.20,
                'payment_terms'=>'15 días','notes'=>'Reposición esencias',
                'items'=>[
                    ['sku'=>'MP-003','qty'=>1000,'price'=>0.0185,'unit'=>'ml','recv'=>1000],
                    ['sku'=>'MP-004','qty'=>800,'price'=>0.022,'unit'=>'ml','recv'=>800],
                    ['sku'=>'MP-005','qty'=>600,'price'=>0.028,'unit'=>'ml','recv'=>600],
                ],
            ],
            // Agosto 2026
            [
                'order_number'=>'OC-2026-003','order_date'=>'2026-08-05',
                'status'=>'sent','received_date'=>null,
                'supplier_code'=>'PROV-001',
                'subtotal'=>1225.00,'tax_rate'=>21,'tax_amount'=>257.25,'total'=>1482.25,
                'payment_terms'=>'30 días','notes'=>'Pedido ampliado por temporada alta',
                'items'=>[
                    ['sku'=>'MP-001','qty'=>100,'price'=>8.50,'unit'=>'kg','recv'=>0],
                    ['sku'=>'ENV-001','qty'=>500,'price'=>1.20,'unit'=>'und','recv'=>0],
                    ['sku'=>'ENV-002','qty'=>300,'price'=>1.55,'unit'=>'und','recv'=>0],
                ],
            ],
            [
                'order_number'=>'OC-2026-004','order_date'=>'2026-08-12',
                'status'=>'draft','received_date'=>null,
                'supplier_code'=>'PROV-004',
                'subtotal'=>466.50,'tax_rate'=>21,'tax_amount'=>97.97,'total'=>564.47,
                'payment_terms'=>'30 días','notes'=>'Insumos cosméticos',
                'items'=>[
                    ['sku'=>'MP-008','qty'=>30,'price'=>8.80,'unit'=>'kg','recv'=>0],
                    ['sku'=>'MP-009','qty'=>5000,'price'=>0.0125,'unit'=>'ml','recv'=>0],
                    ['sku'=>'MP-011','qty'=>2000,'price'=>0.018,'unit'=>'g','recv'=>0],
                ],
            ],
        ];

        $ocIds = [];
        foreach ($purchaseOrders as $oc) {
            // Upsert por order_number
            $existing = DB::table('purchase_orders')->where('order_number', $oc['order_number'])->first();
            if ($existing) {
                $ocId = $existing->id;
            } else {
                $ocId = DB::table('purchase_orders')->insertGetId([
                    'order_number'  => $oc['order_number'],
                    'status'        => $oc['status'],
                    'supplier_id'   => $suppliers[$oc['supplier_code']] ?? 1,
                    'warehouse_id'  => $warehouseId,
                    'order_date'    => $oc['order_date'],
                    'expected_date' => $oc['received_date'] ?? date('Y-m-d', strtotime($oc['order_date'].' +15 days')),
                    'received_date' => $oc['received_date'],
                    'subtotal'      => $oc['subtotal'],
                    'tax_rate'      => $oc['tax_rate'],
                    'tax_amount'    => $oc['tax_amount'],
                    'total'         => $oc['total'],
                    'payment_terms' => $oc['payment_terms'],
                    'notes'         => $oc['notes'],
                    'created_by'    => $adminId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
            $ocIds[$oc['order_number']] = $ocId;

            // Ítems de la OC
            $itemIds = [];
            foreach ($oc['items'] as $item) {
                $itemId = DB::table('purchase_order_items')->insertGetId([
                    'purchase_order_id' => $ocId,
                    'product_id'        => $pid($item['sku']),
                    'description'       => $prods[$item['sku']]->name ?? $item['sku'],
                    'quantity'          => $item['qty'],
                    'received_quantity' => $item['recv'],
                    'unit'              => $item['unit'],
                    'unit_price'        => $item['price'],
                    'discount_percent'  => 0,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
                $itemIds[] = ['item_id'=>$itemId, 'sku'=>$item['sku'], 'qty'=>$item['recv'], 'price'=>$item['price']];
            }

            // Recepciones para OCs recibidas
            if ($oc['status'] === 'received' && $oc['received_date']) {
                $receiptId = DB::table('purchase_receipts')->insertGetId([
                    'receipt_number'    => 'REC-' . str_replace('OC-', '', $oc['order_number']),
                    'purchase_order_id' => $ocId,
                    'warehouse_id'      => $warehouseId,
                    'receipt_date'      => $oc['received_date'],
                    'supplier_invoice'  => 'FAC-' . rand(10000, 99999),
                    'notes'             => 'Recepción completa — OK',
                    'created_by'        => $adminId,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

                foreach ($itemIds as $li) {
                    if ($li['qty'] > 0) {
                        // receipt item (purchase_order_item_id es el último insertado por item)
                        $poItemId = DB::table('purchase_order_items')
                            ->where('purchase_order_id', $ocId)
                            ->where('product_id', $pid($li['sku']))
                            ->value('id');
                        if ($poItemId) {
                            DB::table('purchase_receipt_items')->insert([
                                'purchase_receipt_id'    => $receiptId,
                                'purchase_order_item_id' => $poItemId,
                                'product_id'             => $pid($li['sku']),
                                'quantity'               => $li['qty'],
                                'unit_price'             => $li['price'],
                                'batch_number'           => 'LOTE-' . date('Ymd', strtotime($oc['received_date'])),
                                'created_at'             => $now,
                                'updated_at'             => $now,
                            ]);
                        }
                    }
                }
            }
        }

        // ────────────────────────────────────────────────────────────────────
        // 4. ÓRDENES DE PRODUCCIÓN + MATERIALES
        // ────────────────────────────────────────────────────────────────────
        $recipes = DB::table('recipes')->pluck('id', 'product_id');

        $productionOrders = [
            // Historial Mayo 2026
            [
                'number'=>'OP-2026-00000','product_sku'=>'PT-004','status'=>'finished',
                'planned_qty'=>80,'produced_qty'=>78,'rejected_qty'=>2,
                'planned_start'=>'2026-05-10','planned_end'=>'2026-05-12',
                'started_at'=>'2026-05-10 08:00:00','finished_at'=>'2026-05-12 17:00:00',
                'est_mat'=>332,'est_lab'=>120,'est_ovh'=>60,'est_tot'=>512,
                'act_mat'=>328,'act_lab'=>118,'act_ovh'=>60,'act_tot'=>506,'unit_cost'=>6.49,
                'notes'=>'Producción Jabón Lavanda — Mayo',
            ],
            [
                'number'=>'OP-2026-00000B','product_sku'=>'PT-001','status'=>'finished',
                'planned_qty'=>60,'produced_qty'=>60,'rejected_qty'=>0,
                'planned_start'=>'2026-05-20','planned_end'=>'2026-05-21',
                'started_at'=>'2026-05-20 08:00:00','finished_at'=>'2026-05-21 16:00:00',
                'est_mat'=>485,'est_lab'=>100,'est_ovh'=>50,'est_tot'=>635,
                'act_mat'=>485,'act_lab'=>98,'act_ovh'=>50,'act_tot'=>633,'unit_cost'=>10.55,
                'notes'=>'Producción Velas Lavanda — Mayo',
            ],
            // Junio 2026
            [
                'number'=>'OP-2026-00000C','product_sku'=>'PT-004','status'=>'finished',
                'planned_qty'=>100,'produced_qty'=>99,'rejected_qty'=>1,
                'planned_start'=>'2026-06-10','planned_end'=>'2026-06-12',
                'started_at'=>'2026-06-10 08:00:00','finished_at'=>'2026-06-12 16:30:00',
                'est_mat'=>415,'est_lab'=>150,'est_ovh'=>75,'est_tot'=>640,
                'act_mat'=>412,'act_lab'=>148,'act_ovh'=>75,'act_tot'=>635,'unit_cost'=>6.41,
                'notes'=>'Producción Jabón Lavanda — Junio',
            ],
            // Julio 2026 (existentes en local — insertOrIgnore)
            [
                'number'=>'OP-2026-00001','product_sku'=>'PT-001','status'=>'finished',
                'planned_qty'=>50,'produced_qty'=>50,'rejected_qty'=>0,
                'planned_start'=>'2026-07-11','planned_end'=>'2026-07-13',
                'started_at'=>'2026-07-11 08:00:00','finished_at'=>'2026-07-13 15:00:00',
                'est_mat'=>390,'est_lab'=>90,'est_ovh'=>45,'est_tot'=>525,
                'act_mat'=>390,'act_lab'=>90,'act_ovh'=>45,'act_tot'=>525,'unit_cost'=>10.50,
                'notes'=>'Velas Lavanda — Julio lote 1',
            ],
            [
                'number'=>'OP-2026-00002','product_sku'=>'PT-004','status'=>'finished',
                'planned_qty'=>60,'produced_qty'=>58,'rejected_qty'=>2,
                'planned_start'=>'2026-07-18','planned_end'=>'2026-07-20',
                'started_at'=>'2026-07-18 08:00:00','finished_at'=>'2026-07-20 17:00:00',
                'est_mat'=>249,'est_lab'=>90,'est_ovh'=>45,'est_tot'=>384,
                'act_mat'=>245,'act_lab'=>88,'act_ovh'=>45,'act_tot'=>378,'unit_cost'=>6.52,
                'notes'=>'Jabón Lavanda — Julio lote 1',
            ],
            // Agosto 2026
            [
                'number'=>'OP-2026-00003','product_sku'=>'PT-008','status'=>'in_progress',
                'planned_qty'=>40,'produced_qty'=>20,'rejected_qty'=>0,
                'planned_start'=>'2026-08-10','planned_end'=>'2026-08-12',
                'started_at'=>'2026-08-10 08:00:00','finished_at'=>null,
                'est_mat'=>168,'est_lab'=>60,'est_ovh'=>30,'est_tot'=>258,
                'act_mat'=>84,'act_lab'=>30,'act_ovh'=>15,'act_tot'=>129,'unit_cost'=>0,
                'notes'=>'Galletas Avena — en proceso',
            ],
            [
                'number'=>'OP-2026-00004','product_sku'=>'PT-001','status'=>'planned',
                'planned_qty'=>80,'produced_qty'=>0,'rejected_qty'=>0,
                'planned_start'=>'2026-08-15','planned_end'=>'2026-08-17',
                'started_at'=>null,'finished_at'=>null,
                'est_mat'=>624,'est_lab'=>140,'est_ovh'=>70,'est_tot'=>834,
                'act_mat'=>0,'act_lab'=>0,'act_ovh'=>0,'act_tot'=>0,'unit_cost'=>0,
                'notes'=>'Velas Lavanda — Agosto lote 1',
            ],
            [
                'number'=>'OP-2026-00005','product_sku'=>'PT-004','status'=>'draft',
                'planned_qty'=>100,'produced_qty'=>0,'rejected_qty'=>0,
                'planned_start'=>'2026-08-18','planned_end'=>'2026-08-20',
                'started_at'=>null,'finished_at'=>null,
                'est_mat'=>415,'est_lab'=>150,'est_ovh'=>75,'est_tot'=>640,
                'act_mat'=>0,'act_lab'=>0,'act_ovh'=>0,'act_tot'=>0,'unit_cost'=>0,
                'notes'=>'Jabón Lavanda — Agosto lote 1',
            ],
        ];

        $opIds = [];
        foreach ($productionOrders as $op) {
            $existing = DB::table('production_orders')->where('order_number', $op['number'])->first();
            if ($existing) {
                $opId = $existing->id;
            } else {
                $productId = $pid($op['product_sku']);
                $recipeId  = $recipes[$productId] ?? null;
                $opId = DB::table('production_orders')->insertGetId([
                    'order_number'            => $op['number'],
                    'status'                  => $op['status'],
                    'product_id'              => $productId,
                    'recipe_id'               => $recipeId,
                    'warehouse_id'            => $warehouseId,
                    'planned_quantity'        => $op['planned_qty'],
                    'produced_quantity'       => $op['produced_qty'],
                    'rejected_quantity'       => $op['rejected_qty'],
                    'estimated_material_cost' => $op['est_mat'],
                    'estimated_labor_cost'    => $op['est_lab'],
                    'estimated_overhead_cost' => $op['est_ovh'],
                    'estimated_total_cost'    => $op['est_tot'],
                    'actual_material_cost'    => $op['act_mat'],
                    'actual_labor_cost'       => $op['act_lab'],
                    'actual_overhead_cost'    => $op['act_ovh'],
                    'actual_total_cost'       => $op['act_tot'],
                    'unit_cost'               => $op['unit_cost'],
                    'planned_start_date'      => $op['planned_start'],
                    'planned_end_date'        => $op['planned_end'],
                    'started_at'              => $op['started_at'],
                    'finished_at'             => $op['finished_at'],
                    'created_by'              => $adminId,
                    'notes'                   => $op['notes'],
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ]);
            }
            $opIds[$op['number']] = ['id' => $opId, 'product_sku' => $op['product_sku']];
        }

        // Materiales para OP terminadas/en proceso (Vela Lavanda y Jabón Lavanda)
        $recipeMaterials = [
            'PT-001' => [  // Vela Lavanda — por unidad
                ['sku'=>'MP-001','plan_qty_per_unit'=>0.150,'unit'=>'kg','cost'=>8.50],
                ['sku'=>'MP-003','plan_qty_per_unit'=>20.0, 'unit'=>'ml','cost'=>0.0185],
                ['sku'=>'MP-006','plan_qty_per_unit'=>2.0,  'unit'=>'g', 'cost'=>0.0120],
                ['sku'=>'ENV-001','plan_qty_per_unit'=>1.0, 'unit'=>'und','cost'=>1.20],
                ['sku'=>'ENV-005','plan_qty_per_unit'=>0.15,'unit'=>'m', 'cost'=>0.60],
                ['sku'=>'ENV-006','plan_qty_per_unit'=>1.0, 'unit'=>'und','cost'=>0.080],
            ],
            'PT-004' => [  // Jabón Lavanda — por unidad
                ['sku'=>'MP-008','plan_qty_per_unit'=>0.085,'unit'=>'kg','cost'=>8.80],
                ['sku'=>'MP-009','plan_qty_per_unit'=>10.0, 'unit'=>'ml','cost'=>0.0125],
                ['sku'=>'MP-003','plan_qty_per_unit'=>5.0,  'unit'=>'ml','cost'=>0.0185],
                ['sku'=>'MP-006','plan_qty_per_unit'=>1.0,  'unit'=>'g', 'cost'=>0.0120],
                ['sku'=>'ENV-006','plan_qty_per_unit'=>1.0, 'unit'=>'und','cost'=>0.080],
                ['sku'=>'ENV-007','plan_qty_per_unit'=>1.0, 'unit'=>'und','cost'=>0.045],
            ],
            'PT-008' => [  // Galletas Avena — por unidad
                ['sku'=>'MP-012','plan_qty_per_unit'=>0.200,'unit'=>'kg','cost'=>3.80],
                ['sku'=>'MP-013','plan_qty_per_unit'=>0.080,'unit'=>'kg','cost'=>2.50],
                ['sku'=>'MP-014','plan_qty_per_unit'=>0.100,'unit'=>'kg','cost'=>7.20],
                ['sku'=>'ENV-004','plan_qty_per_unit'=>1.0, 'unit'=>'und','cost'=>0.85],
                ['sku'=>'ENV-006','plan_qty_per_unit'=>1.0, 'unit'=>'und','cost'=>0.080],
            ],
        ];

        foreach ($opIds as $num => $op) {
            $mats = $recipeMaterials[$op['product_sku']] ?? [];
            if (empty($mats)) {
                continue;
            }
            // Buscar la producción para saber la cantidad planificada
            $po = DB::table('production_orders')->where('id', $op['id'])->first();
            if (! $po || DB::table('production_order_materials')->where('production_order_id', $op['id'])->count() > 0) {
                continue;
            }
            $plannedQty  = (float) $po->planned_quantity;
            $consumedQty = (float) $po->produced_quantity;

            foreach ($mats as $mat) {
                DB::table('production_order_materials')->insert([
                    'production_order_id' => $op['id'],
                    'product_id'          => $pid($mat['sku']),
                    'planned_quantity'    => round($plannedQty  * $mat['plan_qty_per_unit'], 4),
                    'consumed_quantity'   => round($consumedQty * $mat['plan_qty_per_unit'], 4),
                    'unit'                => $mat['unit'],
                    'unit_cost'           => $mat['cost'],
                    'is_reserved'         => false,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }
        }

        // ────────────────────────────────────────────────────────────────────
        // 5. PAGOS DE VENTAS (para ventas paid e invoiced)
        // ────────────────────────────────────────────────────────────────────
        $paidSales = DB::table('sales')
            ->whereIn('status', ['paid', 'invoiced'])
            ->whereNull('deleted_at')
            ->get();

        foreach ($paidSales as $sale) {
            // Solo si no tiene pagos aún
            if (DB::table('sale_payments')->where('sale_id', $sale->id)->count() > 0) {
                continue;
            }
            $methods = ['transfer', 'cash', 'check', 'card'];
            DB::table('sale_payments')->insert([
                'sale_id'      => $sale->id,
                'amount'       => $sale->total,
                'method'       => $sale->status === 'paid' ? 'transfer' : $methods[array_rand($methods)],
                'payment_date' => date('Y-m-d', strtotime($sale->sale_date . ' +' . rand(1, 5) . ' days')),
                'reference'    => 'TRF-' . rand(100000, 999999),
                'notes'        => 'Pago recibido conforme',
                'created_by'   => $adminId,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // ────────────────────────────────────────────────────────────────────
        // 6. AJUSTE DE INVENTARIO — alertas realistas
        //    stock_status: critical = qty <= min ; low = qty <= min*1.5
        // ────────────────────────────────────────────────────────────────────
        $inventoryUpdates = [
            // Materias primas que se consumen rápido → alertas
            'MP-001' => ['qty' => 5.00,   'avg_cost' => 8.50],   // critical (min=5)
            'MP-002' => ['qty' => 3.00,   'avg_cost' => 12.00],  // low (min=2, 2*1.5=3)
            'MP-008' => ['qty' => 2.80,   'avg_cost' => 8.80],   // critical (min=3)
            'MP-012' => ['qty' => 8.00,   'avg_cost' => 3.80],   // low (min=10, but 8<10→critical!)
            'MP-013' => ['qty' => 6.00,   'avg_cost' => 2.50],   // low (min=5, 5*1.5=7.5)
            // Productos terminados con nivel bajo
            'PT-006' => ['qty' => 12.00,  'avg_cost' => 9.20],   // low (min=10, 10*1.5=15)
            'PT-007' => ['qty' => 7.00,   'avg_cost' => 14.50],  // low (min=5, 5*1.5=7.5)
            // Envases con stock saludable (actualizar valores promedio)
            'ENV-001' => ['qty' => 85.00,  'avg_cost' => 1.20],
            'ENV-002' => ['qty' => 62.00,  'avg_cost' => 1.55],
            'ENV-003' => ['qty' => 180.00, 'avg_cost' => 0.38],
        ];

        foreach ($inventoryUpdates as $sku => $data) {
            $productId = $pid($sku);
            if (! $productId) {
                continue;
            }
            DB::table('inventory')
                ->where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->update([
                    'quantity'     => $data['qty'],
                    'average_cost' => $data['avg_cost'],
                    'updated_at'   => $now,
                ]);
        }

        // ────────────────────────────────────────────────────────────────────
        // 7. MOVIMIENTOS DE STOCK HISTÓRICOS
        // ────────────────────────────────────────────────────────────────────
        // Solo insertar si no hay movimientos del tipo 'purchase_receipt'
        if (DB::table('stock_movements')->where('movement_type', 'purchase_receipt')->count() === 0) {
            $movements = [
                // Recepciones de compra (Mayo)
                ['type'=>'purchase_receipt','sku'=>'MP-001','qty'=>60,'cost'=>8.50,'date'=>'2026-05-22 10:00:00','ref'=>'REC-2026-000','bal'=>60],
                ['type'=>'purchase_receipt','sku'=>'MP-002','qty'=>25,'cost'=>12.00,'date'=>'2026-05-22 10:05:00','ref'=>'REC-2026-000','bal'=>25],
                // Consumo en producción (Mayo)
                ['type'=>'production_consumption','sku'=>'MP-001','qty'=>-12,'cost'=>8.50,'date'=>'2026-05-20 08:30:00','ref'=>'OP-2026-00000B','bal'=>48],
                ['type'=>'production_consumption','sku'=>'MP-003','qty'=>-1200,'cost'=>0.0185,'date'=>'2026-05-20 08:30:00','ref'=>'OP-2026-00000B','bal'=>2500],
                ['type'=>'production_consumption','sku'=>'MP-008','qty'=>-6.8,'cost'=>8.80,'date'=>'2026-05-10 09:00:00','ref'=>'OP-2026-00000','bal'=>15],
                // Ingreso de producción (Mayo)
                ['type'=>'production_output','sku'=>'PT-001','qty'=>60,'cost'=>10.55,'date'=>'2026-05-21 16:00:00','ref'=>'OP-2026-00000B','bal'=>60],
                ['type'=>'production_output','sku'=>'PT-004','qty'=>78,'cost'=>6.49,'date'=>'2026-05-12 17:00:00','ref'=>'OP-2026-00000','bal'=>78],
                // Ventas (salidas Mayo — ventas de junio)
                ['type'=>'sale_out','sku'=>'PT-001','qty'=>-5,'cost'=>8.50,'date'=>'2026-06-05 14:00:00','ref'=>'VTA-2026-001','bal'=>55],
                ['type'=>'sale_out','sku'=>'PT-004','qty'=>-10,'cost'=>3.50,'date'=>'2026-06-05 14:05:00','ref'=>'VTA-2026-001','bal'=>68],
                ['type'=>'sale_out','sku'=>'PT-006','qty'=>-3,'cost'=>9.20,'date'=>'2026-06-05 14:10:00','ref'=>'VTA-2026-001','bal'=>22],
                // Recepciones Julio
                ['type'=>'purchase_receipt','sku'=>'MP-001','qty'=>50,'cost'=>8.50,'date'=>'2026-07-16 09:00:00','ref'=>'REC-2026-001','bal'=>45],
                ['type'=>'purchase_receipt','sku'=>'MP-002','qty'=>20,'cost'=>12.00,'date'=>'2026-07-16 09:05:00','ref'=>'REC-2026-001','bal'=>20],
                ['type'=>'purchase_receipt','sku'=>'ENV-001','qty'=>300,'cost'=>1.20,'date'=>'2026-07-16 09:10:00','ref'=>'REC-2026-001','bal'=>300],
                ['type'=>'purchase_receipt','sku'=>'MP-003','qty'=>1000,'cost'=>0.0185,'date'=>'2026-07-28 11:00:00','ref'=>'REC-2026-002','bal'=>2500],
                // Producción Julio
                ['type'=>'production_consumption','sku'=>'MP-001','qty'=>-7.5,'cost'=>8.50,'date'=>'2026-07-11 08:30:00','ref'=>'OP-2026-00001','bal'=>37],
                ['type'=>'production_output','sku'=>'PT-001','qty'=>50,'cost'=>10.50,'date'=>'2026-07-13 15:00:00','ref'=>'OP-2026-00001','bal'=>87],
                ['type'=>'production_consumption','sku'=>'MP-008','qty'=>-4.93,'cost'=>8.80,'date'=>'2026-07-18 08:30:00','ref'=>'OP-2026-00002','bal'=>10.07],
                ['type'=>'production_output','sku'=>'PT-004','qty'=>58,'cost'=>6.52,'date'=>'2026-07-20 17:00:00','ref'=>'OP-2026-00002','bal'=>136],
                // Ventas Julio
                ['type'=>'sale_out','sku'=>'PT-004','qty'=>-15,'cost'=>3.50,'date'=>'2026-07-03 10:00:00','ref'=>'VTA-2026-004','bal'=>121],
                ['type'=>'sale_out','sku'=>'PT-005','qty'=>-10,'cost'=>3.80,'date'=>'2026-07-03 10:05:00','ref'=>'VTA-2026-004','bal'=>55],
                ['type'=>'sale_out','sku'=>'PT-001','qty'=>-8,'cost'=>8.50,'date'=>'2026-07-12 11:00:00','ref'=>'VTA-2026-005','bal'=>79],
                // Ajuste de inventario (consumo acumulado)
                ['type'=>'adjustment','sku'=>'MP-001','qty'=>-32,'cost'=>8.50,'date'=>'2026-08-01 07:00:00','ref'=>'AJUSTE-2026-001','bal'=>5],
                ['type'=>'adjustment','sku'=>'MP-008','qty'=>-7.27,'cost'=>8.80,'date'=>'2026-08-01 07:05:00','ref'=>'AJUSTE-2026-001','bal'=>2.8],
            ];

            foreach ($movements as $m) {
                $productId = $pid($m['sku']);
                if (! $productId) {
                    continue;
                }
                DB::table('stock_movements')->insert([
                    'reference_number'    => $m['ref'],
                    'movement_type'       => $m['type'],
                    'product_id'          => $productId,
                    'warehouse_id'        => $warehouseId,
                    'quantity'            => $m['qty'],
                    'unit_cost'           => $m['cost'],
                    'balance_quantity'    => $m['bal'],
                    'balance_average_cost'=> $m['cost'],
                    'balance_total_value' => round(abs($m['bal']) * $m['cost'], 4),
                    'notes'               => ucfirst(str_replace('_', ' ', $m['type'])),
                    'created_by'          => $adminId,
                    'moved_at'            => $m['date'],
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }
        }

        // ────────────────────────────────────────────────────────────────────
        // 8. VENTAS ADICIONALES para clientes nuevos (CLI-006, CLI-007)
        // ────────────────────────────────────────────────────────────────────
        $cli006 = $customers['CLI-006'] ?? null;
        $cli007 = $customers['CLI-007'] ?? null;
        $cli008 = $customers['CLI-008'] ?? null;

        $extraSales = [];
        if ($cli006) {
            $extraSales[] = [
                'order_number'=>'VTA-2026-011','sale_date'=>'2026-07-08','status'=>'paid',
                'customer_id'=>$cli006, 'subtotal'=>148.50,'tax'=>31.19,'total'=>179.69,
                'cog'=>52.00,'method'=>'cash',
                'items'=>[
                    ['sku'=>'PT-004','qty'=>6,'price'=>9.9,'cost'=>3.5],
                    ['sku'=>'PT-009','qty'=>5,'price'=>18.0,'cost'=>7.2],
                ],
            ];
        }
        if ($cli007) {
            $extraSales[] = [
                'order_number'=>'VTA-2026-012','sale_date'=>'2026-08-08','status'=>'invoiced',
                'customer_id'=>$cli007, 'subtotal'=>418.00,'tax'=>87.78,'total'=>505.78,
                'cog'=>148.00,'method'=>'transfer',
                'items'=>[
                    ['sku'=>'PT-001','qty'=>8,'price'=>22.0,'cost'=>8.5],
                    ['sku'=>'PT-007','qty'=>5,'price'=>38.0,'cost'=>14.5],
                    ['sku'=>'PT-010','qty'=>4,'price'=>19.9,'cost'=>6.5],
                ],
            ];
        }
        if ($cli008) {
            $extraSales[] = [
                'order_number'=>'VTA-2026-013','sale_date'=>'2026-08-11','status'=>'confirmed',
                'customer_id'=>$cli008,'subtotal'=>82.90,'tax'=>17.41,'total'=>100.31,
                'cog'=>29.60,'method'=>'cash',
                'items'=>[
                    ['sku'=>'PT-003','qty'=>2,'price'=>32.0,'cost'=>11.8],
                    ['sku'=>'PT-010','qty'=>1,'price'=>19.9,'cost'=>6.5],
                ],
            ];
        }

        foreach ($extraSales as $es) {
            // Solo insertar si no existe
            if (DB::table('sales')->where('order_number', $es['order_number'])->exists()) {
                continue;
            }
            $saleId = DB::table('sales')->insertGetId([
                'order_number'    => $es['order_number'],
                'status'          => $es['status'],
                'type'            => 'sale',
                'customer_id'     => $es['customer_id'],
                'warehouse_id'    => $warehouseId,
                'sale_date'       => $es['sale_date'],
                'subtotal'        => $es['subtotal'],
                'discount_percent'=> 0,
                'discount_amount' => 0,
                'tax_rate'        => 21,
                'tax_amount'      => $es['tax'],
                'total'           => $es['total'],
                'cost_of_goods'   => $es['cog'],
                'currency'        => 'ARS',
                'exchange_rate'   => 1,
                'payment_method'  => $es['method'],
                'seller_id'       => $adminId,
                'created_by'      => $adminId,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            foreach ($es['items'] as $item) {
                DB::table('sale_items')->insert([
                    'sale_id'         => $saleId,
                    'product_id'      => $pid($item['sku']),
                    'description'     => $prods[$item['sku']]->name ?? $item['sku'],
                    'quantity'        => $item['qty'],
                    'unit'            => 'und',
                    'unit_price'      => $item['price'],
                    'unit_cost'       => $item['cost'],
                    'discount_percent'=> 0,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }

            // Pago para ventas pagadas
            if ($es['status'] === 'paid') {
                DB::table('sale_payments')->insert([
                    'sale_id'      => $saleId,
                    'amount'       => $es['total'],
                    'method'       => $es['method'],
                    'payment_date' => date('Y-m-d', strtotime($es['sale_date'] . ' +2 days')),
                    'reference'    => 'TRF-' . rand(100000, 999999),
                    'created_by'   => $adminId,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Eliminar datos de demo (no revertible en producción)
        DB::table('purchase_order_items')->delete();
        DB::table('purchase_receipt_items')->delete();
        DB::table('purchase_receipts')->delete();
        DB::table('purchase_orders')->whereIn('order_number', [
            'OC-2026-000','OC-2026-001','OC-2026-002','OC-2026-003','OC-2026-004',
        ])->delete();
        DB::table('production_order_materials')->delete();
        DB::table('production_orders')->whereIn('order_number', [
            'OP-2026-00000','OP-2026-00000B','OP-2026-00000C',
            'OP-2026-00001','OP-2026-00002','OP-2026-00003','OP-2026-00004','OP-2026-00005',
        ])->delete();
        DB::table('sales')->whereIn('order_number', [
            'VTA-2026-011','VTA-2026-012','VTA-2026-013',
        ])->delete();
        DB::table('customers')->whereIn('code', ['CLI-006','CLI-007','CLI-008'])->delete();
        DB::table('suppliers')->whereIn('code', ['PROV-006','PROV-007'])->delete();
        DB::table('sale_payments')->delete();
        DB::table('stock_movements')->where('movement_type', 'purchase_receipt')->delete();
    }
};
