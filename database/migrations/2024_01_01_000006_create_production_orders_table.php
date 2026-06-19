<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->string('status', 30)->default('draft');

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('recipe_id')->nullable();
            $table->unsignedBigInteger('warehouse_id');

            // Cantidades
            $table->decimal('planned_quantity', 12, 4);
            $table->decimal('produced_quantity', 12, 4)->default(0);
            $table->decimal('rejected_quantity', 12, 4)->default(0);

            // Costos
            $table->decimal('estimated_material_cost', 14, 4)->default(0);
            $table->decimal('estimated_labor_cost', 14, 4)->default(0);
            $table->decimal('estimated_overhead_cost', 14, 4)->default(0);
            $table->decimal('estimated_total_cost', 14, 4)->default(0);
            $table->decimal('actual_material_cost', 14, 4)->default(0);
            $table->decimal('actual_labor_cost', 14, 4)->default(0);
            $table->decimal('actual_overhead_cost', 14, 4)->default(0);
            $table->decimal('actual_total_cost', 14, 4)->default(0);
            $table->decimal('unit_cost', 14, 4)->default(0);

            // Fechas
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            // Responsables
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('recipe_id')->references('id')->on('recipes')->nullOnDelete();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->index(['status', 'planned_start_date']);
            $table->index(['product_id', 'status']);
            $table->index('order_number');
        });

        // Materiales consumidos en la OP
        Schema::create('production_order_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_order_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('planned_quantity', 12, 4);
            $table->decimal('consumed_quantity', 12, 4)->default(0);
            $table->string('unit', 20)->default('und');
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('total_cost', 14, 4)->storedAs('consumed_quantity * unit_cost');
            $table->boolean('is_reserved')->default(false);
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('production_orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products');
            $table->index(['production_order_id', 'product_id']);
        });

        // Historial de estados de OP
        Schema::create('production_order_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_order_id');
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('production_orders')->cascadeOnDelete();
            $table->index('production_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_logs');
        Schema::dropIfExists('production_order_materials');
        Schema::dropIfExists('production_orders');
    }
};
