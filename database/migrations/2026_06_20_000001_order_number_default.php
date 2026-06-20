<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Poner DEFAULT en order_number para que la DB lo genere si el código no lo envía
        DB::statement("
            ALTER TABLE production_orders
            ALTER COLUMN order_number
            SET DEFAULT ('OP-' || to_char(now(), 'YYYY') || '-' || lpad(nextval('production_order_seq')::text, 5, '0'))
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE production_orders ALTER COLUMN order_number DROP DEFAULT");
    }
};
