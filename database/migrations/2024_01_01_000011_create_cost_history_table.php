<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Historial de costos de productos para trazabilidad
        Schema::create('product_cost_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('cost_method', 20);
            $table->decimal('old_cost', 14, 4)->default(0);
            $table->decimal('new_cost', 14, 4)->default(0);
            $table->decimal('variance', 14, 4)->storedAs('new_cost - old_cost');
            $table->string('trigger_type', 50)->nullable();
            $table->unsignedBigInteger('trigger_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index(['product_id', 'created_at']);
            $table->index(['trigger_type', 'trigger_id']);
        });

        // Cola de recálculo de costos
        Schema::create('cost_recalculation_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('trigger', 100);
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index(['processed', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_recalculation_queue');
        Schema::dropIfExists('product_cost_history');
    }
};
