<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar recalculo de costos diario a las 2am
Schedule::command('erp:recalculate-costs')->dailyAt('02:00');

// Limpiar cache de KPIs a medianoche
Schedule::call(function () {
    \Illuminate\Support\Facades\Cache::tags(['kpis', 'reports'])->flush();
})->daily()->name('clear-kpi-cache');

// Snapshots diarios para el historial de costos
Schedule::call(function () {
    \App\Domains\Products\Models\Product::active()->chunk(50, function ($products) {
        foreach ($products as $product) {
            \App\Domains\Inventory\Models\ProductCostSnapshot::create([
                'product_id' => $product->id,
                'average_cost' => $product->average_cost,
                'standard_cost' => $product->standard_cost,
                'snapshotted_at' => now(),
            ]);
        }
    });
})->dailyAt('23:50')->name('cost-snapshots');
