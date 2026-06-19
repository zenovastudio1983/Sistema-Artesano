<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stock actual por producto y almacén
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('quantity', 14, 4)->default(0);
            $table->decimal('reserved_quantity', 14, 4)->default(0);
            $table->decimal('available_quantity', 14, 4)->storedAs('quantity - reserved_quantity');
            $table->decimal('average_cost', 14, 4)->default(0);
            $table->decimal('total_value', 18, 4)->storedAs('quantity * average_cost');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();

            $table->unique(['product_id', 'warehouse_id']);
            $table->index(['product_id', 'warehouse_id', 'quantity']);
        });

        // Movimientos de stock (Kardex)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->nullable();
            $table->string('movement_type', 50);
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('destination_warehouse_id')->nullable();

            // Cantidades
            $table->decimal('quantity', 14, 4);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('total_cost', 18, 4)->storedAs('quantity * unit_cost');

            // Saldo después del movimiento (Kardex)
            $table->decimal('balance_quantity', 14, 4)->default(0);
            $table->decimal('balance_average_cost', 14, 4)->default(0);
            $table->decimal('balance_total_value', 18, 4)->default(0);

            // Referencias polimórficas
            $table->nullableMorphs('moveable');

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('moved_at');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->foreign('destination_warehouse_id')->references('id')->on('warehouses')->nullOnDelete();

            $table->index(['product_id', 'warehouse_id', 'moved_at']);
            $table->index(['movement_type', 'moved_at']);
            $table->index('reference_number');
            $table->index('moveable_type');
        });

        // Lotes de productos
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->string('batch_number', 60);
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 14, 4)->default(0);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->unique(['product_id', 'warehouse_id', 'batch_number']);
            $table->index(['product_id', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory');
    }
};
