<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->string('status', 30)->default('quotation');
            $table->string('type', 20)->default('sale');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('warehouse_id');

            // Fechas
            $table->date('sale_date');
            $table->date('due_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->timestamp('confirmed_at')->nullable();

            // Totales
            $table->decimal('subtotal', 18, 4)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(18);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('total', 18, 4)->default(0);
            $table->decimal('cost_of_goods', 18, 4)->default(0);
            $table->decimal('gross_profit', 18, 4)->storedAs('total - cost_of_goods');
            $table->string('currency', 10)->default('PEN');
            $table->decimal('exchange_rate', 10, 6)->default(1);

            // Facturación
            $table->string('invoice_number', 60)->nullable();
            $table->string('invoice_series', 10)->nullable();
            $table->date('invoice_date')->nullable();

            $table->string('payment_method', 50)->nullable();
            $table->string('payment_terms', 100)->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('reference', 100)->nullable();

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->index(['status', 'sale_date']);
            $table->index(['customer_id', 'status']);
            $table->index('order_number');
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('product_id');
            $table->string('description', 200)->nullable();
            $table->decimal('quantity', 12, 4);
            $table->string('unit', 20)->default('und');
            $table->decimal('unit_price', 14, 4);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 14, 4)->storedAs('quantity * unit_price * discount_percent / 100');
            $table->decimal('subtotal', 18, 4)->storedAs('quantity * unit_price - quantity * unit_price * discount_percent / 100');
            $table->decimal('cost_total', 18, 4)->storedAs('quantity * unit_cost');
            $table->decimal('margin', 18, 4)->storedAs('(quantity * unit_price - quantity * unit_price * discount_percent / 100) - (quantity * unit_cost)');
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('sales')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products');
            $table->index(['sale_id', 'product_id']);
        });

        // Pagos recibidos
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->decimal('amount', 18, 4);
            $table->string('method', 50)->default('cash');
            $table->date('payment_date');
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('sales')->cascadeOnDelete();
            $table->index(['sale_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
