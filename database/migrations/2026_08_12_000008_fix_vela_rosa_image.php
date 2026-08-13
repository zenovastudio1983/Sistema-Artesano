<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where('name', 'Vela Aromática Rosa 300ml')
            ->update([
                'storefront_image_url' => 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=600&h=600&fit=crop',
            ]);
    }

    public function down(): void
    {
        DB::table('products')
            ->where('name', 'Vela Aromática Rosa 300ml')
            ->update([
                'storefront_image_url' => 'https://images.unsplash.com/photo-1599683875523-85f2f8a76e6f?w=600&h=600&fit=crop',
            ]);
    }
};
