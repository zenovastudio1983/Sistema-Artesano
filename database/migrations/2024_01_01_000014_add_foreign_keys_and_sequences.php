<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add FK from users to warehouses after both tables exist
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('default_warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
        });

        // FKs to users table (users is created in migration 000012, after these tables)
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('production_orders', function (Blueprint $table) {
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('purchase_receipts', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('seller_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // Sequences for order numbers
        DB::statement("CREATE SEQUENCE IF NOT EXISTS purchase_order_seq START 1 INCREMENT 1");
        DB::statement("CREATE SEQUENCE IF NOT EXISTS production_order_seq START 1 INCREMENT 1");
        DB::statement("CREATE SEQUENCE IF NOT EXISTS sale_seq START 1 INCREMENT 1");
        DB::statement("CREATE SEQUENCE IF NOT EXISTS quotation_seq START 1 INCREMENT 1");
        DB::statement("CREATE SEQUENCE IF NOT EXISTS purchase_receipt_seq START 1 INCREMENT 1");
        DB::statement("CREATE SEQUENCE IF NOT EXISTS supplier_code_seq START 1 INCREMENT 1");
        DB::statement("CREATE SEQUENCE IF NOT EXISTS customer_code_seq START 1 INCREMENT 1");
        DB::statement("CREATE SEQUENCE IF NOT EXISTS product_sku_seq START 1 INCREMENT 1");

        // PostgreSQL functions for order number generation
        DB::statement("
            CREATE OR REPLACE FUNCTION generate_order_number(prefix VARCHAR, seq_name VARCHAR)
            RETURNS VARCHAR AS \$\$
            DECLARE
                seq_val BIGINT;
                year_part VARCHAR;
            BEGIN
                seq_val := nextval(seq_name);
                year_part := TO_CHAR(NOW(), 'YYYY');
                RETURN prefix || '-' || year_part || '-' || LPAD(seq_val::TEXT, 5, '0');
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // View para inventario consolidado con alertas
        DB::statement("
            CREATE OR REPLACE VIEW v_inventory_status AS
            SELECT
                p.id AS product_id,
                p.sku,
                p.name AS product_name,
                p.type AS product_type,
                c.name AS category_name,
                p.unit,
                p.stock_minimum,
                COALESCE(SUM(i.quantity), 0) AS total_stock,
                COALESCE(SUM(i.reserved_quantity), 0) AS total_reserved,
                COALESCE(SUM(i.quantity) - SUM(i.reserved_quantity), 0) AS available_stock,
                COALESCE(AVG(i.average_cost), p.cost) AS current_cost,
                COALESCE(SUM(i.total_value), 0) AS total_inventory_value,
                CASE
                    WHEN COALESCE(SUM(i.quantity), 0) = 0 THEN 'out_of_stock'
                    WHEN COALESCE(SUM(i.quantity), 0) <= p.stock_minimum THEN 'critical'
                    WHEN COALESCE(SUM(i.quantity), 0) <= p.stock_minimum * 1.5 THEN 'low'
                    ELSE 'ok'
                END AS stock_status,
                p.status AS product_status
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN inventory i ON i.product_id = p.id
                AND i.warehouse_id IN (SELECT id FROM warehouses WHERE is_active = TRUE)
            WHERE p.deleted_at IS NULL
            GROUP BY p.id, p.sku, p.name, p.type, c.name, p.unit, p.stock_minimum, p.cost, p.status;
        ");

        // View para KPIs del dashboard
        DB::statement("
            CREATE OR REPLACE VIEW v_dashboard_kpis AS
            SELECT
                -- Ventas del mes
                COALESCE(SUM(CASE WHEN s.status IN ('confirmed','invoiced','paid')
                    AND DATE_TRUNC('month', s.sale_date) = DATE_TRUNC('month', CURRENT_DATE)
                    THEN s.total ELSE 0 END), 0) AS monthly_sales,
                -- Costo de ventas del mes
                COALESCE(SUM(CASE WHEN s.status IN ('confirmed','invoiced','paid')
                    AND DATE_TRUNC('month', s.sale_date) = DATE_TRUNC('month', CURRENT_DATE)
                    THEN s.cost_of_goods ELSE 0 END), 0) AS monthly_cost_of_goods,
                -- Margen bruto del mes
                COALESCE(SUM(CASE WHEN s.status IN ('confirmed','invoiced','paid')
                    AND DATE_TRUNC('month', s.sale_date) = DATE_TRUNC('month', CURRENT_DATE)
                    THEN s.gross_profit ELSE 0 END), 0) AS monthly_gross_profit,
                -- Ventas del año
                COALESCE(SUM(CASE WHEN s.status IN ('confirmed','invoiced','paid')
                    AND DATE_PART('year', s.sale_date) = DATE_PART('year', CURRENT_DATE)
                    THEN s.total ELSE 0 END), 0) AS yearly_sales,
                -- Compras pendientes
                (SELECT COUNT(*) FROM purchase_orders WHERE status IN ('draft','sent')) AS pending_purchases,
                -- OPs activas
                (SELECT COUNT(*) FROM production_orders WHERE status IN ('planned','in_progress')) AS active_production_orders,
                -- Productos bajo stock crítico
                (SELECT COUNT(*) FROM v_inventory_status WHERE stock_status IN ('critical','out_of_stock') AND product_status = 'active') AS critical_stock_count
            FROM sales s
            WHERE s.deleted_at IS NULL;
        ");
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::table('purchase_receipts', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);
        });

        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        DB::statement("DROP VIEW IF EXISTS v_dashboard_kpis");
        DB::statement("DROP VIEW IF EXISTS v_inventory_status");
        DB::statement("DROP FUNCTION IF EXISTS generate_order_number(VARCHAR, VARCHAR)");
        DB::statement("DROP SEQUENCE IF EXISTS customer_code_seq");
        DB::statement("DROP SEQUENCE IF EXISTS supplier_code_seq");
        DB::statement("DROP SEQUENCE IF EXISTS purchase_receipt_seq");
        DB::statement("DROP SEQUENCE IF EXISTS quotation_seq");
        DB::statement("DROP SEQUENCE IF EXISTS sale_seq");
        DB::statement("DROP SEQUENCE IF EXISTS production_order_seq");
        DB::statement("DROP SEQUENCE IF EXISTS purchase_order_seq");
        DB::statement("DROP SEQUENCE IF EXISTS product_sku_seq");

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['default_warehouse_id']);
        });
    }
};
