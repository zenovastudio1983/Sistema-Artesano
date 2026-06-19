<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Materias Primas', 'color' => '#7C3AED', 'icon' => 'beaker', 'children' => [
                ['name' => 'Ceras y Parafinas', 'color' => '#8B5CF6'],
                ['name' => 'Aceites Esenciales', 'color' => '#A78BFA'],
                ['name' => 'Colorantes', 'color' => '#C4B5FD'],
                ['name' => 'Fragancias', 'color' => '#DDD6FE'],
                ['name' => 'Bases Cosméticas', 'color' => '#7C3AED'],
                ['name' => 'Harinas y Cereales', 'color' => '#6D28D9'],
                ['name' => 'Endulzantes', 'color' => '#5B21B6'],
                ['name' => 'Conservantes', 'color' => '#4C1D95'],
            ]],
            ['name' => 'Envases y Empaques', 'color' => '#0891B2', 'icon' => 'cube', 'children' => [
                ['name' => 'Frascos de Vidrio', 'color' => '#0E7490'],
                ['name' => 'Frascos Plásticos', 'color' => '#155E75'],
                ['name' => 'Cajas y Estuches', 'color' => '#164E63'],
                ['name' => 'Etiquetas', 'color' => '#0891B2'],
                ['name' => 'Tapas y Cierres', 'color' => '#06B6D4'],
                ['name' => 'Bolsas y Pouches', 'color' => '#67E8F9'],
            ]],
            ['name' => 'Velas Artesanales', 'color' => '#D97706', 'icon' => 'fire', 'children' => [
                ['name' => 'Velas de Soja', 'color' => '#B45309'],
                ['name' => 'Velas de Cera de Abeja', 'color' => '#92400E'],
                ['name' => 'Velas Decorativas', 'color' => '#78350F'],
                ['name' => 'Velas Aromáticas', 'color' => '#D97706'],
            ]],
            ['name' => 'Jabones y Cosméticos', 'color' => '#059669', 'icon' => 'sparkles', 'children' => [
                ['name' => 'Jabones de Glicerina', 'color' => '#047857'],
                ['name' => 'Jabones Artesanales', 'color' => '#065F46'],
                ['name' => 'Cremas y Lociones', 'color' => '#059669'],
                ['name' => 'Shampoo y Acondicionador', 'color' => '#10B981'],
                ['name' => 'Aceites Corporales', 'color' => '#34D399'],
            ]],
            ['name' => 'Alimentos Artesanales', 'color' => '#DC2626', 'icon' => 'cake', 'children' => [
                ['name' => 'Panes y Galletas', 'color' => '#B91C1C'],
                ['name' => 'Conservas y Mermeladas', 'color' => '#991B1B'],
                ['name' => 'Chocolates y Dulces', 'color' => '#7F1D1D'],
                ['name' => 'Infusiones y Tés', 'color' => '#DC2626'],
            ]],
            ['name' => 'Suministros', 'color' => '#6B7280', 'icon' => 'wrench', 'children' => [
                ['name' => 'Pabilos y Mechas', 'color' => '#4B5563'],
                ['name' => 'Moldes', 'color' => '#374151'],
                ['name' => 'Herramientas', 'color' => '#1F2937'],
                ['name' => 'Material de Limpieza', 'color' => '#6B7280'],
            ]],
        ];

        $sortOrder = 0;
        foreach ($categories as $cat) {
            $parentId = DB::table('categories')->insertGetId([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
                'color' => $cat['color'],
                'icon' => $cat['icon'] ?? null,
                'sort_order' => $sortOrder++,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (isset($cat['children'])) {
                $childOrder = 0;
                foreach ($cat['children'] as $child) {
                    DB::table('categories')->insert([
                        'parent_id' => $parentId,
                        'name' => $child['name'],
                        'slug' => Str::slug($child['name']) . '-' . $parentId,
                        'color' => $child['color'],
                        'sort_order' => $childOrder++,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
