<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where('name', 'Kit Spa Lavanda (Jabón+Vela)')
            ->update(['public_slug' => 'kit-spa-lavanda-jabon-vela-29']);
    }

    public function down(): void {}
};
