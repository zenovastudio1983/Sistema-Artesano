<?php

namespace Database\Factories;

use App\Domains\Inventory\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);
        return [
            'code'        => strtoupper(Str::slug($name, '')),
            'name'        => ucwords($name),
            'description' => fake()->optional()->sentence(),
            'location'    => fake()->optional()->address(),
            'is_default'  => false,
            'is_active'   => true,
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
