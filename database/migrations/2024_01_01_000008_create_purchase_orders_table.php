<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->string('status', 30)->default('draft');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('warehouse_id');

            // Fechas
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->date('received_date')->nullable();

            // Totales
            $table->decimal('subtotal', 18, 4)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->decimal('shipping_cost', 14, 4)->default(0);
            $table->decimal('total', 18, 4)->default(0);
            $table->string('currency', 10)->default('PEN');
            $table->decimal('exchange_rate', 10, 6)->default(1);

            // Condiciones
            $table->string('payment_terms', 100)->nullable();
            $table->string('delivery_terms', 100)->nullable();
            $table->string('reference', 100)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->index(['status', 'order_date']);
            $table->index(['supplier_id', 'status']);
            $table->index('order_number');
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('product_id');
            $table->string('description', 200)->nullable();
            $table->decimal('quantity', 12, 4);
            $table->decimal('received_quantity', 12, 4)->default(0);
            $table->string('unit', 20)->default('und');
            $table->decimal('unit_price', 14, 4);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 14, 4)->storedAs('quantity * unit_price * discount_percent / 100');
            $table->decimal('subtotal', 18, 4)->storedAs('quantity * unit_price - quantity * unit_price * discount_percent / 100');
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products');
            $table->index(['purchase_order_id', 'product_id']);
        });

        // Recepciones de mercadería
        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 30)->unique();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->date('receipt_date');
            $table->string('supplier_invoice', 60)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->index(['purchase_order_id', 'receipt_date']);
        });

        Schema::create('purchase_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_receipt_id');
            $table->unsignedBigInteger('purchase_order_item_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 14, 4);
            $table->string('batch_number', 60)->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->foreign('purchase_receipt_id')->references('id')->on('purchase_receipts')->cascadeOnDelete();
            $table->foreign('purchase_order_item_id')->references('id')->on('purchase_order_items');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipt_items');
        Schema::dropIfExists('purchase_receipts');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
