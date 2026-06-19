<?php

namespace App\Jobs;

use App\Domains\Products\Models\Product;
use App\Domains\Recipes\Services\RecipeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecalculateProductCosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private int $productId,
        private string $trigger = 'manual'
    ) {
        $this->onQueue('costs');
    }

    public function handle(RecipeService $recipeService): void
    {
        $product = Product::find($this->productId);

        if (!$product) {
            return;
        }

        $recipeService->recalculateAllCostsForProduct($product);

        // Recalcular costos de productos que usen este como ingrediente
        $parentProducts = Product::whereHas('recipes.ingredients', function ($q) use ($product) {
            $q->where('product_id', $product->id);
        })->get();

        foreach ($parentProducts as $parentProduct) {
            self::dispatch($parentProduct->id, "cascade_from_{$this->productId}")
                ->delay(now()->addSeconds(30));
        }

        Log::info("Product costs recalculated", [
            'product_id' => $this->productId,
            'sku' => $product->sku,
            'trigger' => $this->trigger,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Failed to recalculate costs for product {$this->productId}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
