<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where('name', 'Brownies Artesanales de Cacao 200g')
            ->update([
                'storefront_image_url' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&h=600&fit=crop',
            ]);

        // Corregir slug con caracteres especiales del nombre "Kit Spa Lavanda (Jabón+Vela)"
        DB::table('products')
            ->where('name', 'Kit Spa Lavanda (Jabón+Vela)')
            ->update([
                'public_slug' => 'kit-spa-lavanda-jabon-vela-29',
            ]);
    }

    public function down(): void {}
};
