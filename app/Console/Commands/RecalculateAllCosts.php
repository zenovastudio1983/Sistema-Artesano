<?php

namespace App\Console\Commands;

use App\Domains\Products\Models\Product;
use App\Domains\Recipes\Services\RecipeService;
use App\Support\Enums\ProductType;
use Illuminate\Console\Command;

class RecalculateAllCosts extends Command
{
    protected $signature = 'erp:recalculate-costs
        {--product= : ID of specific product to recalculate}
        {--force : Force recalculation even if recently done}';

    protected $description = 'Recalculate costs for all products with active recipes';

    public function handle(RecipeService $recipeService): int
    {
        $this->info('Starting cost recalculation...');

        $query = Product::whereIn('type', [
            ProductType::FinishedProduct->value,
            ProductType::SemiFinished->value,
        ])->with(['recipes' => fn($q) => $q->where('is_active', true)]);

        if ($productId = $this->option('product')) {
            $query->where('id', $productId);
        }

        $products = $query->get();

        $this->withProgressBar($products, function ($product) use ($recipeService) {
            try {
                $recipeService->recalculateAllCostsForProduct($product);
            } catch (\Exception $e) {
                $this->newLine();
                $this->warn("Error recalculating {$product->sku}: {$e->getMessage()}");
            }
        });

        $this->newLine();
        $this->info("Recalculated costs for {$products->count()} products.");

        return Command::SUCCESS;
    }
}
