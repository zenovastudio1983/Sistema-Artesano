<?php

namespace Database\Factories;

use App\Domains\Purchases\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    private static int $codeCounter = 100;

    public function definition(): array
    {
        return [
            'code'          => sprintf('PROV-%04d', self::$codeCounter++),
            'business_name' => fake()->company(),
            'trade_name'    => fake()->optional()->company(),
            'tax_id'        => fake()->optional()->numerify('##########'),
            'email'         => fake()->optional()->companyEmail(),
            'phone'         => fake()->optional()->phoneNumber(),
            'address'       => fake()->optional()->address(),
            'city'          => fake()->optional()->city(),
            'payment_days'  => fake()->randomElement([0, 15, 30, 45, 60]),
            'currency'      => 'PEN',
            'is_active'     => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function withPaymentDays(int $days): static
    {
        return $this->state(fn () => ['payment_days' => $days]);
    }
}
