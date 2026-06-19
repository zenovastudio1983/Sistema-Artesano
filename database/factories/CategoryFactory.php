<?php

namespace Database\Factories;

use App\Domains\Products\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->words(fake()->numberBetween(1, 2), true);
        return [
            'name'        => ucwords($name),
            'slug'        => Str::slug($name).'-'.Str::random(4),
            'description' => fake()->optional()->sentence(),
            'color'       => fake()->optional()->hexColor(),
            'icon'        => fake()->optional()->randomElement(['tag', 'box', 'package', 'layers']),
            'sort_order'  => fake()->numberBetween(0, 100),
            'is_active'   => true,
            'parent_id'   => null,
        ];
    }

    public function withParent(Category $parent): static
    {
        return $this->state(fn () => ['parent_id' => $parent->id]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
