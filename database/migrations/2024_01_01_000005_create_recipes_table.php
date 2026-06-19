<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Encabezado de receta
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            // Rendimiento
            $table->decimal('yield_quantity', 12, 4)->default(1);
            $table->string('yield_unit', 20)->default('und');

            // Costos calculados
            $table->decimal('material_cost', 14, 4)->default(0);
            $table->decimal('labor_cost', 14, 4)->default(0);
            $table->decimal('overhead_cost', 14, 4)->default(0);
            $table->decimal('total_cost', 14, 4)->default(0);
            $table->decimal('unit_cost', 14, 4)->default(0);

            // Tiempo de producción en minutos
            $table->unsignedInteger('production_time_minutes')->default(0);

            $table->text('instructions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('costed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index(['product_id', 'is_active', 'is_default']);
            $table->unique(['product_id', 'version']);
        });

        // Líneas de receta (ingredientes)
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 12, 6);
            $table->string('unit', 20)->default('und');
            $table->decimal('scrap_percentage', 5, 2)->default(0);
            $table->decimal('net_quantity', 12, 6)->storedAs('quantity * (1 + scrap_percentage / 100)');
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('total_cost', 14, 4)->storedAs('quantity * (1 + scrap_percentage / 100) * unit_cost');
            $table->boolean('is_optional')->default(false);
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('recipe_id')->references('id')->on('recipes')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products');
            $table->index(['recipe_id', 'sort_order']);
        });

        // Costos adicionales de receta (mano de obra, indirectos)
        Schema::create('recipe_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_id');
            $table->string('cost_type', 30);
            $table->string('description', 200);
            $table->decimal('amount', 14, 4)->default(0);
            $table->boolean('is_per_unit')->default(true);
            $table->timestamps();

            $table->foreign('recipe_id')->references('id')->on('recipes')->cascadeOnDelete();
            $table->index('recipe_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_costs');
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('recipes');
    }
};
