<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->date('manufacturing_date')->nullable()->after('shelf_life_days');
            $table->date('expiry_date')->nullable()->after('manufacturing_date');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['manufacturing_date', 'expiry_date']);
        });
    }
};
