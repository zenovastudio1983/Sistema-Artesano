<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where('name', 'Aceite Corporal Relajante 100ml')
            ->update([
                'storefront_image_url' => 'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?w=600&h=600&fit=crop',
            ]);
    }

    public function down(): void
    {
        DB::table('products')
            ->where('name', 'Aceite Corporal Relajante 100ml')
            ->update([
                'storefront_image_url' => 'https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?w=600&h=600&fit=crop',
            ]);
    }
};
