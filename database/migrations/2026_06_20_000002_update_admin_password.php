<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'admin@artisanerp.local')
            ->update(['password' => Hash::make('SistemaCFP2026')]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', 'admin@artisanerp.local')
            ->update(['password' => Hash::make('Admin@ERP2024!')]);
    }
};
