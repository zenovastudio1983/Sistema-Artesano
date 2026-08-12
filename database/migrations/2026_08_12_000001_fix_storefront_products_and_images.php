<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Imágenes por nombre de producto (Unsplash, CC-free)
        $images = [
            'Vela Aromática Lavanda 200ml'       => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=600&h=600&fit=crop',
            'Vela Aromática Vainilla 200ml'      => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?w=600&h=600&fit=crop',
            'Vela Aromática Rosa 300ml'          => 'https://images.unsplash.com/photo-1599683875523-85f2f8a76e6f?w=600&h=600&fit=crop',
            'Jabón Artesanal Lavanda 100g'       => 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?w=600&h=600&fit=crop',
            'Jabón Artesanal Carbón 100g'        => 'https://images.unsplash.com/photo-1612817288484-6f916006741a?w=600&h=600&fit=crop',
            'Crema Corporal Karité 200ml'        => 'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?w=600&h=600&fit=crop',
            'Kit Spa Lavanda (Jabón+Vela)'       => 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=600&h=600&fit=crop',
            'Galletas Artesanales de Avena 250g' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=600&h=600&fit=crop',
            'Brownies Artesanales de Cacao 200g' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&h=600&fit=crop',
            'Aceite Corporal Relajante 100ml'    => 'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?w=600&h=600&fit=crop',
        ];

        // Productos finales que deben estar en la tienda
        $storefront = array_keys($images);

        // 1. Despublicar todos (limpieza)
        DB::table('products')->update([
            'is_published'    => false,
            'public_slug'     => null,
            'is_made_to_order'=> false,
            'lead_time_days'  => null,
        ]);

        // 2. Publicar y agregar imagen a cada producto de tienda
        foreach ($images as $name => $url) {
            $product = DB::table('products')->where('name', $name)->first();
            if (!$product) {
                continue;
            }

            $slug = \Illuminate\Support\Str::slug($name) . '-' . $product->id;

            DB::table('products')->where('id', $product->id)->update([
                'is_published'        => true,
                'public_slug'         => $slug,
                'storefront_image_url'=> $url,
                'is_made_to_order'    => false,
                'lead_time_days'      => null,
            ]);
        }
    }

    public function down(): void
    {
        // No revertible — datos de producción
    }
};
