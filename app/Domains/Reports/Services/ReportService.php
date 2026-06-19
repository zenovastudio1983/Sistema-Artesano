<?php

namespace App\Domains\Reports\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getDashboardKpis(): array
    {
        return Cache::remember('dashboard.kpis', config('erp.cache.dashboard_kpis', 300), function () {
            $currentMonth = now()->format('Y-m');
            $lastMonth = now()->subMonth()->format('Y-m');
            $currentYear = now()->year;

            $monthlySales = DB::selectOne("
                SELECT
                    COALESCE(SUM(CASE WHEN DATE_TRUNC('month', sale_date) = DATE_TRUNC('month', CURRENT_DATE)
                        AND status IN ('confirmed','invoiced','paid') THEN total ELSE 0 END), 0) AS current_month,
                    COALESCE(SUM(CASE WHEN DATE_TRUNC('month', sale_date) = DATE_TRUNC('month', CURRENT_DATE - INTERVAL '1 month')
                        AND status IN ('confirmed','invoiced','paid') THEN total ELSE 0 END), 0) AS last_month
                FROM sales WHERE deleted_at IS NULL
            ");

            $monthlyProduction = DB::selectOne("
                SELECT
                    COUNT(*) AS total_orders,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS active_orders,
                    COALESCE(SUM(CASE WHEN DATE_TRUNC('month', finished_at) = DATE_TRUNC('month', CURRENT_DATE)
                        THEN actual_total_cost ELSE 0 END), 0) AS monthly_production_cost,
                    COALESCE(SUM(CASE WHEN DATE_TRUNC('month', finished_at) = DATE_TRUNC('month', CURRENT_DATE)
                        THEN produced_quantity ELSE 0 END), 0) AS monthly_produced_units
                FROM production_orders WHERE deleted_at IS NULL
            ");

            $inventory = DB::selectOne("
                SELECT
                    COUNT(DISTINCT CASE WHEN vis.stock_status IN ('critical','out_of_stock') THEN vis.product_id END) AS critical_count,
                    COUNT(DISTINCT CASE WHEN vis.stock_status = 'low' THEN vis.product_id END) AS low_count,
                    COALESCE(SUM(vis.total_inventory_value), 0) AS total_value
                FROM v_inventory_status vis
                WHERE vis.product_status = 'active'
            ");

            $grossProfit = DB::selectOne("
                SELECT
                    COALESCE(SUM(CASE WHEN DATE_TRUNC('month', sale_date) = DATE_TRUNC('month', CURRENT_DATE)
                        AND status IN ('confirmed','invoiced','paid') THEN gross_profit ELSE 0 END), 0) AS monthly_profit,
                    COALESCE(SUM(CASE WHEN DATE_PART('year', sale_date) = DATE_PART('year', CURRENT_DATE)
                        AND status IN ('confirmed','invoiced','paid') THEN gross_profit ELSE 0 END), 0) AS yearly_profit
                FROM sales WHERE deleted_at IS NULL
            ");

            $pendingPurchases = DB::selectOne("
                SELECT COUNT(*) AS count, COALESCE(SUM(total), 0) AS total_amount
                FROM purchase_orders WHERE status IN ('draft','sent') AND deleted_at IS NULL
            ");

            return [
                'monthly_sales' => (float) $monthlySales->current_month,
                'last_month_sales' => (float) $monthlySales->last_month,
                'sales_growth' => $monthlySales->last_month > 0
                    ? round((($monthlySales->current_month - $monthlySales->last_month) / $monthlySales->last_month) * 100, 1)
                    : 0,
                'monthly_production_cost' => (float) $monthlyProduction->monthly_production_cost,
                'monthly_produced_units' => (float) $monthlyProduction->monthly_produced_units,
                'active_production_orders' => (int) $monthlyProduction->active_orders,
                'monthly_gross_profit' => (float) $grossProfit->monthly_profit,
                'yearly_gross_profit' => (float) $grossProfit->yearly_profit,
                'gross_margin_percent' => $monthlySales->current_month > 0
                    ? round(($grossProfit->monthly_profit / $monthlySales->current_month) * 100, 1)
                    : 0,
                'critical_stock_count' => (int) $inventory->critical_count,
                'low_stock_count' => (int) $inventory->low_count,
                'total_inventory_value' => (float) $inventory->total_value,
                'pending_purchases_count' => (int) $pendingPurchases->count,
                'pending_purchases_amount' => (float) $pendingPurchases->total_amount,
            ];
        });
    }

    public function getSalesReport(string $from, string $to, ?int $customerId = null, ?string $groupBy = 'day'): array
    {
        $query = DB::table('sales')
            ->join('customers', 'customers.id', '=', 'sales.customer_id')
            ->whereIn('sales.status', ['confirmed', 'invoiced', 'paid'])
            ->whereBetween('sales.sale_date', [$from, $to])
            ->whereNull('sales.deleted_at');

        if ($customerId) {
            $query->where('sales.customer_id', $customerId);
        }

        $groupExpr = match($groupBy) {
            'day' => "TO_CHAR(sale_date, 'YYYY-MM-DD')",
            'week' => "TO_CHAR(DATE_TRUNC('week', sale_date), 'YYYY-MM-DD')",
            'month' => "TO_CHAR(DATE_TRUNC('month', sale_date), 'YYYY-MM')",
            default => "TO_CHAR(sale_date, 'YYYY-MM-DD')",
        };

        $summary = $query->clone()
            ->selectRaw("
                {$groupExpr} as period,
                COUNT(*) as order_count,
                SUM(total) as total_sales,
                SUM(cost_of_goods) as total_cost,
                SUM(gross_profit) as total_profit,
                CASE WHEN SUM(total) > 0 THEN ROUND(SUM(gross_profit) / SUM(total) * 100, 2) ELSE 0 END as margin_percent
            ")
            ->groupByRaw($groupExpr)
            ->orderByRaw($groupExpr)
            ->get();

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereIn('sales.status', ['confirmed', 'invoiced', 'paid'])
            ->whereBetween('sales.sale_date', [$from, $to])
            ->whereNull('sales.deleted_at')
            ->selectRaw('
                products.id, products.sku, products.name, products.unit,
                SUM(sale_items.quantity) as total_quantity,
                SUM(sale_items.subtotal) as total_revenue,
                SUM(sale_items.margin) as total_margin,
                CASE WHEN SUM(sale_items.subtotal) > 0
                    THEN ROUND(SUM(sale_items.margin) / SUM(sale_items.subtotal) * 100, 2)
                    ELSE 0 END as margin_percent
            ')
            ->groupBy('products.id', 'products.sku', 'products.name', 'products.unit')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        $totals = $query->clone()
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total) as total_sales,
                SUM(cost_of_goods) as total_cost,
                SUM(gross_profit) as total_profit,
                CASE WHEN SUM(total) > 0 THEN ROUND(SUM(gross_profit)/SUM(total)*100,2) ELSE 0 END as margin_percent
            ')
            ->first();

        return [
            'summary' => $summary,
            'top_products' => $topProducts,
            'totals' => $totals,
            'period' => ['from' => $from, 'to' => $to],
        ];
    }

    public function getPurchasesReport(string $from, string $to, ?int $supplierId = null): array
    {
        $query = DB::table('purchase_orders')
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->whereIn('purchase_orders.status', ['received', 'partially_received'])
            ->whereBetween('purchase_orders.order_date', [$from, $to])
            ->whereNull('purchase_orders.deleted_at');

        if ($supplierId) {
            $query->where('purchase_orders.supplier_id', $supplierId);
        }

        $bySupplier = $query->clone()
            ->selectRaw('
                suppliers.id, suppliers.business_name, suppliers.trade_name,
                COUNT(*) as order_count,
                SUM(total) as total_amount
            ')
            ->groupBy('suppliers.id', 'suppliers.business_name', 'suppliers.trade_name')
            ->orderByDesc('total_amount')
            ->get();

        $totals = $query->clone()
            ->selectRaw('COUNT(*) as total_orders, SUM(total) as total_amount')
            ->first();

        return [
            'by_supplier' => $bySupplier,
            'totals' => $totals,
            'period' => ['from' => $from, 'to' => $to],
        ];
    }

    public function getInventoryReport(): array
    {
        $inventory = DB::table('v_inventory_status')
            ->orderBy('product_name')
            ->get();

        $summary = [
            'total_products' => $inventory->count(),
            'total_value' => $inventory->sum('total_inventory_value'),
            'critical_count' => $inventory->where('stock_status', 'critical')->count(),
            'out_of_stock_count' => $inventory->where('stock_status', 'out_of_stock')->count(),
            'low_count' => $inventory->where('stock_status', 'low')->count(),
            'by_category' => DB::table('v_inventory_status')
                ->selectRaw('category_name, SUM(total_inventory_value) as value, COUNT(*) as products')
                ->groupBy('category_name')
                ->orderByDesc('value')
                ->get(),
        ];

        return [
            'items' => $inventory,
            'summary' => $summary,
        ];
    }

    public function getProductionCostReport(string $from, string $to): array
    {
        $orders = DB::table('production_orders')
            ->join('products', 'products.id', '=', 'production_orders.product_id')
            ->where('production_orders.status', 'finished')
            ->whereBetween('production_orders.finished_at', [$from, $to . ' 23:59:59'])
            ->whereNull('production_orders.deleted_at')
            ->selectRaw('
                products.sku, products.name, products.unit,
                SUM(produced_quantity) as total_produced,
                SUM(actual_material_cost) as material_cost,
                SUM(actual_labor_cost) as labor_cost,
                SUM(actual_overhead_cost) as overhead_cost,
                SUM(actual_total_cost) as total_cost,
                CASE WHEN SUM(produced_quantity) > 0
                    THEN ROUND(SUM(actual_total_cost)/SUM(produced_quantity), 4)
                    ELSE 0 END as unit_cost,
                SUM(estimated_total_cost) as estimated_cost,
                SUM(actual_total_cost) - SUM(estimated_total_cost) as variance
            ')
            ->groupBy('products.sku', 'products.name', 'products.unit')
            ->orderByDesc('total_cost')
            ->get();

        $totals = DB::table('production_orders')
            ->where('status', 'finished')
            ->whereBetween('finished_at', [$from, $to . ' 23:59:59'])
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(produced_quantity) as total_units,
                SUM(actual_total_cost) as total_cost,
                SUM(estimated_total_cost) as estimated_cost
            ')
            ->first();

        return [
            'orders' => $orders,
            'totals' => $totals,
            'period' => ['from' => $from, 'to' => $to],
        ];
    }

    public function invalidateCache(): void
    {
        Cache::forget('dashboard.kpis');
    }
}
