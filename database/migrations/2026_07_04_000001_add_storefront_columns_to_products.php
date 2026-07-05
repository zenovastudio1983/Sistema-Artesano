<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('notes');
            $table->string('public_slug', 120)->unique()->nullable()->after('is_published');
            $table->text('public_description')->nullable()->after('public_slug');
            $table->boolean('is_made_to_order')->default(false)->after('public_description');
            $table->unsignedSmallInteger('lead_time_days')->nullable()->after('is_made_to_order');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_published', 'public_slug', 'public_description', 'is_made_to_order', 'lead_time_days']);
        });
    }
};
