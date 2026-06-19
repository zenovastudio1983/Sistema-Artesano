<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Materias primas
        $rawMaterials = [
            ['sku' => 'MP-001', 'name' => 'Cera de Soja 464', 'unit' => 'kg', 'cost' => 18.50, 'stock_minimum' => 5, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-002', 'name' => 'Cera de Abeja Natural', 'unit' => 'kg', 'cost' => 45.00, 'stock_minimum' => 2, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-003', 'name' => 'Esencia Lavanda Premium', 'unit' => 'ml', 'cost' => 0.15, 'stock_minimum' => 500, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-004', 'name' => 'Esencia Vainilla Natural', 'unit' => 'ml', 'cost' => 0.12, 'stock_minimum' => 500, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-005', 'name' => 'Esencia Rosa Mosqueta', 'unit' => 'ml', 'cost' => 0.20, 'stock_minimum' => 300, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-006', 'name' => 'Colorante Rojo en Polvo', 'unit' => 'g', 'cost' => 0.08, 'stock_minimum' => 200, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-007', 'name' => 'Colorante Azul Marino', 'unit' => 'g', 'cost' => 0.09, 'stock_minimum' => 200, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-008', 'name' => 'Base Jabón de Glicerina', 'unit' => 'kg', 'cost' => 12.00, 'stock_minimum' => 3, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-009', 'name' => 'Aceite de Coco Virgen', 'unit' => 'ml', 'cost' => 0.025, 'stock_minimum' => 1000, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-010', 'name' => 'Aceite de Jojoba', 'unit' => 'ml', 'cost' => 0.045, 'stock_minimum' => 500, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-011', 'name' => 'Manteca de Karité', 'unit' => 'g', 'cost' => 0.03, 'stock_minimum' => 500, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-012', 'name' => 'Harina de Trigo Artesanal', 'unit' => 'kg', 'cost' => 4.50, 'stock_minimum' => 10, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-013', 'name' => 'Azúcar Rubia Orgánica', 'unit' => 'kg', 'cost' => 5.50, 'stock_minimum' => 5, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-014', 'name' => 'Mantequilla Sin Sal', 'unit' => 'kg', 'cost' => 22.00, 'stock_minimum' => 2, 'type' => 'raw_material', 'is_purchasable' => true],
            ['sku' => 'MP-015', 'name' => 'Cacao en Polvo Premium', 'unit' => 'g', 'cost' => 0.035, 'stock_minimum' => 500, 'type' => 'raw_material', 'is_purchasable' => true],
        ];

        // Envases
        $packaging = [
            ['sku' => 'ENV-001', 'name' => 'Vaso de Vidrio 200ml con Tapa', 'unit' => 'und', 'cost' => 2.80, 'stock_minimum' => 50, 'type' => 'packaging', 'is_purchasable' => true],
            ['sku' => 'ENV-002', 'name' => 'Vaso de Vidrio 300ml con Tapa', 'unit' => 'und', 'cost' => 3.50, 'stock_minimum' => 50, 'type' => 'packaging', 'is_purchasable' => true],
            ['sku' => 'ENV-003', 'name' => 'Frasco PET 100ml Blanco', 'unit' => 'und', 'cost' => 0.90, 'stock_minimum' => 100, 'type' => 'packaging', 'is_purchasable' => true],
            ['sku' => 'ENV-004', 'name' => 'Caja Kraft 15x8cm', 'unit' => 'und', 'cost' => 0.45, 'stock_minimum' => 100, 'type' => 'packaging', 'is_purchasable' => true],
            ['sku' => 'ENV-005', 'name' => 'Pabilo de Algodón #4 (m)', 'unit' => 'm', 'cost' => 0.08, 'stock_minimum' => 500, 'type' => 'supply', 'is_purchasable' => true],
            ['sku' => 'ENV-006', 'name' => 'Etiqueta Impresa Full Color', 'unit' => 'und', 'cost' => 0.25, 'stock_minimum' => 200, 'type' => 'packaging', 'is_purchasable' => true],
            ['sku' => 'ENV-007', 'name' => 'Bolsa Celofán 15x25cm', 'unit' => 'und', 'cost' => 0.15, 'stock_minimum' => 200, 'type' => 'packaging', 'is_purchasable' => true],
        ];

        // Productos terminados
        $finishedProducts = [
            ['sku' => 'PT-001', 'name' => 'Vela Aromática Lavanda 200ml', 'unit' => 'und', 'cost' => 8.50, 'price' => 22.00, 'stock_minimum' => 10, 'type' => 'finished_product', 'is_sellable' => true, 'is_producible' => true],
            ['sku' => 'PT-002', 'name' => 'Vela Aromática Vainilla 200ml', 'unit' => 'und', 'cost' => 8.20, 'price' => 22.00, 'stock_minimum' => 10, 'type' => 'finished_product', 'is_sellable' => true, 'is_producible' => true],
            ['sku' => 'PT-003', 'name' => 'Vela Aromática Rosa 300ml', 'unit' => 'und', 'cost' => 11.80, 'price' => 32.00, 'stock_minimum' => 8, 'type' => 'finished_product', 'is_sellable' => true, 'is_producible' => true],
            ['sku' => 'PT-004', 'name' => 'Jabón Artesanal Lavanda 100g', 'unit' => 'und', 'cost' => 3.50, 'price' => 9.90, 'stock_minimum' => 20, 'type' => 'finished_product', 'is_sellable' => true, 'is_producible' => true],
            ['sku' => 'PT-005', 'name' => 'Jabón Artesanal Carbón 100g', 'unit' => 'und', 'cost' => 3.80, 'price' => 10.90, 'stock_minimum' => 20, 'type' => 'finished_product', 'is_sellable' => true, 'is_producible' => true],
            ['sku' => 'PT-006', 'name' => 'Crema Corporal Karité 200ml', 'unit' => 'und', 'cost' => 9.20, 'price' => 28.00, 'stock_minimum' => 10, 'type' => 'finished_product', 'is_sellable' => true, 'is_producible' => true],
            ['sku' => 'PT-007', 'name' => 'Kit Spa Lavanda (Jabón+Vela)', 'unit' => 'und', 'cost' => 14.50, 'price' => 38.00, 'stock_minimum' => 5, 'type' => 'finished_product', 'is_sellable' => true, 'is_producible' => true],
            ['sku' => 'PT-008', 'name' => 'Galletas Artesanales de Avena 250g', 'unit' => 'und', 'cost' => 5.80, 'price' => 15.00, 'stock_minimum' => 15, 'type' => 'finished_product', 'is_sellable' => true, 'is_producible' => true],
            ['sku' => 'PT-009', 'name' => 'Brownies Artesanales de Cacao 200g', 'unit' => 'und', 'cost' => 7.20, 'price' => 18.00, 'stock_minimum' => 10, 'type' => 'finished_product', 'is_sellable' => true, 'is_producible' => true],
            ['sku' => 'PT-010', 'name' => 'Aceite Corporal Relajante 100ml', 'unit' => 'und', 'cost' => 6.50, 'price' => 19.90, 'stock_minimum' => 10, 'type' => 'finished_product', 'is_sellable' => true, 'is_producible' => true],
        ];

        $allProducts = array_merge($rawMaterials, $packaging, $finishedProducts);

        foreach ($allProducts as $product) {
            DB::table('products')->insertOrIgnore(array_merge([
                'cost' => 0,
                'standard_cost' => $product['cost'] ?? 0,
                'average_cost' => $product['cost'] ?? 0,
                'price' => $product['price'] ?? 0,
                'stock_minimum' => 0,
                'is_purchasable' => false,
                'is_sellable' => false,
                'is_producible' => false,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ], $product));
        }
    }
}
