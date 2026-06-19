<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('business_name', 200);
            $table->string('trade_name', 150)->nullable();
            $table->string('tax_id', 30)->nullable();
            $table->string('tax_type', 10)->default('RUC');
            $table->string('customer_type', 20)->default('retail');
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->default('Perú');
            $table->string('contact_name', 150)->nullable();
            $table->unsignedSmallInteger('payment_days')->default(0);
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->decimal('current_balance', 14, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->string('price_list', 20)->default('regular');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'business_name']);
            $table->index('tax_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
