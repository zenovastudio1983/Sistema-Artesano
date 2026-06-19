<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        $finishedProducts = DB::table('products')
            ->where('type', 'finished_product')
            ->get()
            ->keyBy('sku');

        $rawMaterials = DB::table('products')
            ->whereIn('type', ['raw_material', 'packaging', 'supply'])
            ->get()
            ->keyBy('sku');

        // Receta: Vela Aromática Lavanda 200ml
        if (isset($finishedProducts['PT-001'])) {
            $recipeId = DB::table('recipes')->insertGetId([
                'product_id' => $finishedProducts['PT-001']->id,
                'name' => 'Vela Lavanda 200ml v1',
                'version' => 1,
                'yield_quantity' => 1,
                'yield_unit' => 'und',
                'production_time_minutes' => 45,
                'is_active' => true,
                'is_default' => true,
                'instructions' => "1. Pesar la cera de soja\n2. Fundir la cera a 80°C\n3. Agregar colorante y mezclar\n4. Bajar temperatura a 60°C\n5. Agregar esencia de lavanda y mezclar\n6. Fijar el pabilo en el vaso\n7. Verter la mezcla en el vaso\n8. Dejar enfriar 24 horas",
                'labor_cost' => 1.50,
                'overhead_cost' => 0.50,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ingredients = [
                ['sku' => 'MP-001', 'qty' => 0.150, 'unit' => 'kg'],
                ['sku' => 'MP-003', 'qty' => 20, 'unit' => 'ml'],
                ['sku' => 'MP-006', 'qty' => 2, 'unit' => 'g'],
                ['sku' => 'ENV-001', 'qty' => 1, 'unit' => 'und'],
                ['sku' => 'ENV-005', 'qty' => 0.15, 'unit' => 'm'],
                ['sku' => 'ENV-006', 'qty' => 1, 'unit' => 'und'],
            ];

            foreach ($ingredients as $i => $ing) {
                if (isset($rawMaterials[$ing['sku']])) {
                    $prod = $rawMaterials[$ing['sku']];
                    DB::table('recipe_ingredients')->insert([
                        'recipe_id' => $recipeId,
                        'product_id' => $prod->id,
                        'quantity' => $ing['qty'],
                        'unit' => $ing['unit'],
                        'unit_cost' => $prod->cost,
                        'sort_order' => $i,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Receta: Jabón Artesanal Lavanda 100g
        if (isset($finishedProducts['PT-004'])) {
            $recipeId = DB::table('recipes')->insertGetId([
                'product_id' => $finishedProducts['PT-004']->id,
                'name' => 'Jabón Lavanda 100g v1',
                'version' => 1,
                'yield_quantity' => 1,
                'yield_unit' => 'und',
                'production_time_minutes' => 30,
                'is_active' => true,
                'is_default' => true,
                'instructions' => "1. Fundir la base de glicerina al baño maría\n2. Agregar aceite de coco y lavanda\n3. Mezclar con colorante\n4. Verter en molde\n5. Dejar solidificar 2 horas\n6. Desmoldar y envolver",
                'labor_cost' => 0.80,
                'overhead_cost' => 0.20,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ingredients = [
                ['sku' => 'MP-008', 'qty' => 0.085, 'unit' => 'kg'],
                ['sku' => 'MP-009', 'qty' => 10, 'unit' => 'ml'],
                ['sku' => 'MP-003', 'qty' => 5, 'unit' => 'ml'],
                ['sku' => 'MP-006', 'qty' => 1, 'unit' => 'g'],
                ['sku' => 'ENV-006', 'qty' => 1, 'unit' => 'und'],
                ['sku' => 'ENV-007', 'qty' => 1, 'unit' => 'und'],
            ];

            foreach ($ingredients as $i => $ing) {
                if (isset($rawMaterials[$ing['sku']])) {
                    $prod = $rawMaterials[$ing['sku']];
                    DB::table('recipe_ingredients')->insert([
                        'recipe_id' => $recipeId,
                        'product_id' => $prod->id,
                        'quantity' => $ing['qty'],
                        'unit' => $ing['unit'],
                        'unit_cost' => $prod->cost,
                        'sort_order' => $i,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Receta: Galletas Artesanales de Avena
        if (isset($finishedProducts['PT-008'])) {
            $recipeId = DB::table('recipes')->insertGetId([
                'product_id' => $finishedProducts['PT-008']->id,
                'name' => 'Galletas de Avena 250g v1',
                'version' => 1,
                'yield_quantity' => 1,
                'yield_unit' => 'und',
                'production_time_minutes' => 60,
                'is_active' => true,
                'is_default' => true,
                'instructions' => "1. Mezclar harina, azúcar y mantequilla\n2. Agregar huevo y mezclar bien\n3. Formar las galletas en bandeja\n4. Hornear 180°C por 12 minutos\n5. Dejar enfriar y empacar",
                'labor_cost' => 1.20,
                'overhead_cost' => 0.80,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ingredients = [
                ['sku' => 'MP-012', 'qty' => 0.200, 'unit' => 'kg'],
                ['sku' => 'MP-013', 'qty' => 0.080, 'unit' => 'kg'],
                ['sku' => 'MP-014', 'qty' => 0.100, 'unit' => 'kg'],
                ['sku' => 'ENV-004', 'qty' => 1, 'unit' => 'und'],
                ['sku' => 'ENV-006', 'qty' => 1, 'unit' => 'und'],
            ];

            foreach ($ingredients as $i => $ing) {
                if (isset($rawMaterials[$ing['sku']])) {
                    $prod = $rawMaterials[$ing['sku']];
                    DB::table('recipe_ingredients')->insert([
                        'recipe_id' => $recipeId,
                        'product_id' => $prod->id,
                        'quantity' => $ing['qty'],
                        'unit' => $ing['unit'],
                        'unit_cost' => $prod->cost,
                        'sort_order' => $i,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
