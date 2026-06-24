<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Suppliers
        DB::statement("ALTER TABLE suppliers ALTER COLUMN country SET DEFAULT 'Argentina'");
        DB::statement("ALTER TABLE suppliers ALTER COLUMN currency SET DEFAULT 'ARS'");
        DB::statement("ALTER TABLE suppliers ALTER COLUMN tax_type SET DEFAULT 'CUIT'");

        // Customers
        DB::statement("ALTER TABLE customers ALTER COLUMN country SET DEFAULT 'Argentina'");
        DB::statement("ALTER TABLE customers ALTER COLUMN tax_type SET DEFAULT 'CUIT'");

        // Purchase orders
        DB::statement("ALTER TABLE purchase_orders ALTER COLUMN currency SET DEFAULT 'ARS'");
        DB::statement("ALTER TABLE purchase_orders ALTER COLUMN tax_rate SET DEFAULT 21");

        // Sales
        DB::statement("ALTER TABLE sales ALTER COLUMN currency SET DEFAULT 'ARS'");
        DB::statement("ALTER TABLE sales ALTER COLUMN tax_rate SET DEFAULT 21");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE suppliers ALTER COLUMN country SET DEFAULT 'Perú'");
        DB::statement("ALTER TABLE suppliers ALTER COLUMN currency SET DEFAULT 'PEN'");
        DB::statement("ALTER TABLE suppliers ALTER COLUMN tax_type SET DEFAULT 'RUC'");

        DB::statement("ALTER TABLE customers ALTER COLUMN country SET DEFAULT 'Perú'");
        DB::statement("ALTER TABLE customers ALTER COLUMN tax_type SET DEFAULT 'RUC'");

        DB::statement("ALTER TABLE purchase_orders ALTER COLUMN currency SET DEFAULT 'PEN'");
        DB::statement("ALTER TABLE purchase_orders ALTER COLUMN tax_rate SET DEFAULT 0");

        DB::statement("ALTER TABLE sales ALTER COLUMN currency SET DEFAULT 'PEN'");
        DB::statement("ALTER TABLE sales ALTER COLUMN tax_rate SET DEFAULT 18");
    }
};
