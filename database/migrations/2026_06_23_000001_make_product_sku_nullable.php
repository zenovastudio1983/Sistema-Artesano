<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // sku y unit se envían como null cuando el usuario los deja vacíos
            $table->string('sku', 50)->nullable()->unique()->change();
            $table->string('unit', 20)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 50)->nullable(false)->change();
            $table->string('unit', 20)->nullable(false)->default('und')->change();
        });
    }
};
