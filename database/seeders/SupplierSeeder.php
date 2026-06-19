<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'code' => 'PROV-001',
                'business_name' => 'Distribuidora Nacional de Ceras SAC',
                'trade_name' => 'DistrCeras',
                'tax_id' => '20345678901',
                'email' => 'ventas@distrceras.com',
                'phone' => '01-555-0001',
                'contact_name' => 'Roberto Quispe',
                'payment_days' => 30,
                'currency' => 'PEN',
            ],
            [
                'code' => 'PROV-002',
                'business_name' => 'Aromas del Perú EIRL',
                'trade_name' => 'AromasPeru',
                'tax_id' => '20456789012',
                'email' => 'info@aromasperu.pe',
                'phone' => '01-555-0002',
                'contact_name' => 'Carmen Valle',
                'payment_days' => 15,
                'currency' => 'PEN',
            ],
            [
                'code' => 'PROV-003',
                'business_name' => 'Envases y Empaques del Norte SRL',
                'trade_name' => 'EnvasesNorte',
                'tax_id' => '20567890123',
                'email' => 'pedidos@envasesnorte.com',
                'phone' => '01-555-0003',
                'contact_name' => 'Luis Herrera',
                'payment_days' => 45,
                'currency' => 'PEN',
            ],
            [
                'code' => 'PROV-004',
                'business_name' => 'Insumos Cosméticos del Perú SA',
                'trade_name' => 'InsuCosme',
                'tax_id' => '20678901234',
                'email' => 'comercial@insucosme.pe',
                'phone' => '01-555-0004',
                'contact_name' => 'Patricia Morales',
                'payment_days' => 30,
                'currency' => 'PEN',
            ],
            [
                'code' => 'PROV-005',
                'business_name' => 'Agro Ingredientes Naturales SAC',
                'trade_name' => 'AgroNat',
                'tax_id' => '20789012345',
                'email' => 'pedidos@agronat.pe',
                'phone' => '01-555-0005',
                'contact_name' => 'Andrés Castillo',
                'payment_days' => 0,
                'currency' => 'PEN',
            ],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('suppliers')->insertOrIgnore(array_merge($supplier, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
