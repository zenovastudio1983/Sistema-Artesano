<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('barcode', 100)->nullable()->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('type', 30)->default('raw_material');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('unit', 20)->default('und');
            $table->string('secondary_unit', 20)->nullable();
            $table->decimal('conversion_factor', 12, 6)->default(1);

            // Costos
            $table->decimal('cost', 14, 4)->default(0);
            $table->decimal('standard_cost', 14, 4)->default(0);
            $table->decimal('last_purchase_cost', 14, 4)->default(0);
            $table->decimal('average_cost', 14, 4)->default(0);

            // Precio de venta
            $table->decimal('price', 14, 4)->default(0);
            $table->decimal('min_price', 14, 4)->default(0);
            $table->decimal('margin_percent', 8, 2)->default(0);

            // Stock
            $table->decimal('stock_minimum', 12, 4)->default(0);
            $table->decimal('stock_maximum', 12, 4)->nullable();
            $table->decimal('reorder_point', 12, 4)->default(0);

            // Control de lotes y vencimiento
            $table->boolean('track_batches')->default(false);
            $table->boolean('track_expiry')->default(false);
            $table->unsignedSmallInteger('shelf_life_days')->nullable();

            // Dimensiones y peso (para logística)
            $table->decimal('weight', 10, 4)->nullable();
            $table->string('weight_unit', 5)->default('g');
            $table->decimal('volume', 10, 4)->nullable();
            $table->string('volume_unit', 5)->default('ml');

            $table->string('status', 20)->default('active');
            $table->boolean('is_purchasable')->default(true);
            $table->boolean('is_sellable')->default(false);
            $table->boolean('is_producible')->default(false);

            $table->json('meta')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();

            $table->index(['type', 'status']);
            $table->index(['category_id', 'status']);
            $table->index('sku');
            $table->index('barcode');

            // Full-text search con PostgreSQL
            $table->rawIndex(
                "to_tsvector('spanish', coalesce(name,'') || ' ' || coalesce(sku,'') || ' ' || coalesce(description,''))",
                'products_fts_idx',
                'gin'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
