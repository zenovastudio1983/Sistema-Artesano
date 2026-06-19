<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'code' => 'CLI-001',
                'business_name' => 'Boutique Natural Lima',
                'trade_name' => 'Natural Lima',
                'tax_id' => '20111222333',
                'customer_type' => 'wholesale',
                'email' => 'compras@naturallima.com',
                'phone' => '01-222-0001',
                'payment_days' => 30,
                'discount_percent' => 10,
            ],
            [
                'code' => 'CLI-002',
                'business_name' => 'Spa Holístico Wellness',
                'trade_name' => 'Wellness Spa',
                'tax_id' => '20222333444',
                'customer_type' => 'wholesale',
                'email' => 'pedidos@wellnessspa.pe',
                'phone' => '01-222-0002',
                'payment_days' => 15,
                'discount_percent' => 8,
            ],
            [
                'code' => 'CLI-003',
                'business_name' => 'Tienda Eco Market',
                'trade_name' => 'Eco Market',
                'tax_id' => '20333444555',
                'customer_type' => 'retail',
                'email' => 'info@ecomarket.pe',
                'phone' => '01-222-0003',
                'payment_days' => 0,
                'discount_percent' => 5,
            ],
            [
                'code' => 'CLI-004',
                'business_name' => 'García Vargas María Elena',
                'customer_type' => 'retail',
                'tax_id' => '10445566778',
                'tax_type' => 'DNI',
                'email' => 'maria.garcia@gmail.com',
                'phone' => '999-888-001',
                'payment_days' => 0,
                'discount_percent' => 0,
            ],
            [
                'code' => 'CLI-005',
                'business_name' => 'Hotel Artesanal Plaza',
                'trade_name' => 'Hotel Plaza',
                'tax_id' => '20555666777',
                'customer_type' => 'corporate',
                'email' => 'compras@hotelplaza.pe',
                'phone' => '01-222-0005',
                'payment_days' => 60,
                'credit_limit' => 5000,
                'discount_percent' => 15,
            ],
        ];

        foreach ($customers as $customer) {
            DB::table('customers')->insertOrIgnore(array_merge([
                'credit_limit' => 0,
                'discount_percent' => 0,
                'price_list' => 'regular',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], $customer));
        }
    }
}
