<?php

namespace Database\Factories;

use App\Domains\Sales\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    private static int $codeCounter = 100;

    public function definition(): array
    {
        return [
            'code'             => sprintf('CLI-%04d', self::$codeCounter++),
            'business_name'    => fake()->company(),
            'trade_name'       => fake()->optional()->company(),
            'tax_id'           => fake()->optional()->numerify('##########'),
            'tax_type'         => fake()->optional()->randomElement(['RUC', 'DNI', 'CE']),
            'customer_type'    => fake()->randomElement(['retail', 'wholesale', 'corporate']),
            'email'            => fake()->optional()->safeEmail(),
            'phone'            => fake()->optional()->phoneNumber(),
            'address'          => fake()->optional()->address(),
            'payment_days'     => fake()->randomElement([0, 15, 30]),
            'credit_limit'     => fake()->optional()->randomFloat(2, 0, 10000),
            'discount_percent' => 0,
            'is_active'        => true,
        ];
    }

    public function retail(): static
    {
        return $this->state(fn () => ['customer_type' => 'retail']);
    }

    public function wholesale(): static
    {
        return $this->state(fn () => ['customer_type' => 'wholesale', 'discount_percent' => 10]);
    }

    public function corporate(): static
    {
        return $this->state(fn () => [
            'customer_type' => 'corporate',
            'credit_limit'  => 50000,
            'payment_days'  => 30,
        ]);
    }

    public function withCreditLimit(float $limit): static
    {
        return $this->state(fn () => ['credit_limit' => $limit]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
