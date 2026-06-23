<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // v_inventory_status depende de products.sku — hay que eliminarla antes de alterar la columna
        DB::statement("DROP VIEW IF EXISTS v_inventory_status");

        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 50)->nullable()->unique()->change();
            $table->string('unit', 20)->nullable()->default(null)->change();
        });

        // Recrear la vista tal como estaba
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
            GROUP BY p.id, p.sku, p.name, p.type, c.name, p.unit, p.stock_minimum, p.cost, p.status
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_inventory_status");

        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 50)->nullable(false)->change();
            $table->string('unit', 20)->nullable(false)->default('und')->change();
        });

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
            GROUP BY p.id, p.sku, p.name, p.type, c.name, p.unit, p.stock_minimum, p.cost, p.status
        ");
    }
};
