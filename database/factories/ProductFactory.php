<?php

namespace Database\Factories;

use App\Domains\Products\Models\Product;
use App\Support\Enums\ProductStatus;
use App\Support\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    private static int $skuCounter = 1;

    public function definition(): array
    {
        $name = fake()->words(fake()->numberBetween(1, 3), true);
        $type = fake()->randomElement(ProductType::cases());
        $cost = fake()->randomFloat(2, 1, 500);

        return [
            'sku'                => sprintf('TEST-%04d', self::$skuCounter++),
            'name'               => ucwords($name),
            'description'        => fake()->optional()->sentence(),
            'type'               => $type,
            'unit'               => fake()->randomElement(['und', 'kg', 'lt', 'mt', 'g', 'ml']),
            'cost'               => $cost,
            'standard_cost'      => $cost,
            'average_cost'       => $cost,
            'last_purchase_cost' => $cost,
            'price'              => $cost * fake()->randomFloat(2, 1.2, 3.0),
            'min_stock'          => fake()->randomFloat(2, 0, 10),
            'reorder_point'      => fake()->randomFloat(2, 5, 20),
            'max_stock'          => fake()->optional()->randomFloat(2, 50, 500),
            'weight'             => fake()->optional()->randomFloat(3, 0.001, 10),
            'status'             => ProductStatus::Active,
            'track_batches'      => false,
        ];
    }

    public function rawMaterial(): static
    {
        return $this->state(fn () => ['type' => ProductType::RawMaterial]);
    }

    public function finishedProduct(): static
    {
        return $this->state(fn () => ['type' => ProductType::FinishedProduct]);
    }

    public function packaging(): static
    {
        return $this->state(fn () => ['type' => ProductType::Packaging]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => ProductStatus::Inactive]);
    }

    public function withLowStock(): static
    {
        return $this->state(fn () => [
            'min_stock'     => 100,
            'reorder_point' => 50,
        ]);
    }

    public function withSku(string $sku): static
    {
        return $this->state(fn () => ['sku' => $sku]);
    }

    public function withCost(float $cost): static
    {
        return $this->state(fn () => [
            'cost'               => $cost,
            'standard_cost'      => $cost,
            'average_cost'       => $cost,
            'last_purchase_cost' => $cost,
        ]);
    }
}
