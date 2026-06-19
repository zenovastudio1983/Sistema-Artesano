<?php

namespace Database\Seeders;

use App\Domains\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard' => ['view dashboard'],

            // Products
            'products' => [
                'view products', 'create products', 'edit products',
                'delete products', 'export products',
            ],

            // Categories
            'categories' => [
                'view categories', 'create categories', 'edit categories', 'delete categories',
            ],

            // Inventory
            'inventory' => [
                'view inventory', 'adjust inventory', 'transfer inventory',
                'view kardex', 'export inventory',
            ],

            // Recipes
            'recipes' => [
                'view recipes', 'create recipes', 'edit recipes', 'delete recipes',
            ],

            // Production
            'production' => [
                'view production', 'create production orders', 'edit production orders',
                'start production', 'finish production', 'cancel production',
            ],

            // Purchases
            'purchases' => [
                'view purchases', 'create purchase orders', 'edit purchase orders',
                'approve purchase orders', 'receive purchase orders', 'cancel purchase orders',
            ],

            // Suppliers
            'suppliers' => [
                'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers',
            ],

            // Sales
            'sales' => [
                'view sales', 'create sales', 'edit sales', 'confirm sales',
                'cancel sales', 'view all sales', 'apply discounts',
            ],

            // Customers
            'customers' => [
                'view customers', 'create customers', 'edit customers', 'delete customers',
            ],

            // Reports
            'reports' => [
                'view reports', 'export reports', 'view cost reports',
                'view financial reports',
            ],

            // Users
            'users' => [
                'view users', 'create users', 'edit users', 'delete users',
                'assign roles',
            ],

            // Settings
            'settings' => ['view settings', 'edit settings'],
        ];

        foreach ($permissions as $group => $groupPermissions) {
            foreach ($groupPermissions as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission, 'guard_name' => 'web'],
                    ['group' => $group]
                );
            }
        }

        // Crear roles del sistema
        $adminRole = Role::firstOrCreate(
            ['name' => 'Administrador', 'guard_name' => 'web'],
            ['description' => 'Acceso total al sistema', 'color' => '#DC2626', 'is_system' => true]
        );
        $adminRole->syncPermissions(Permission::all());

        $productionRole = Role::firstOrCreate(
            ['name' => 'Producción', 'guard_name' => 'web'],
            ['description' => 'Gestión de producción e inventario', 'color' => '#2563EB', 'is_system' => true]
        );
        $productionRole->syncPermissions([
            'view dashboard', 'view products', 'view inventory', 'adjust inventory',
            'view kardex', 'view recipes', 'view production', 'create production orders',
            'edit production orders', 'start production', 'finish production',
        ]);

        $purchasesRole = Role::firstOrCreate(
            ['name' => 'Compras', 'guard_name' => 'web'],
            ['description' => 'Gestión de compras y proveedores', 'color' => '#7C3AED', 'is_system' => true]
        );
        $purchasesRole->syncPermissions([
            'view dashboard', 'view products', 'view inventory',
            'view purchases', 'create purchase orders', 'edit purchase orders',
            'receive purchase orders', 'view suppliers', 'create suppliers', 'edit suppliers',
        ]);

        $salesRole = Role::firstOrCreate(
            ['name' => 'Ventas', 'guard_name' => 'web'],
            ['description' => 'Gestión de ventas y clientes', 'color' => '#059669', 'is_system' => true]
        );
        $salesRole->syncPermissions([
            'view dashboard', 'view products', 'view inventory',
            'view sales', 'create sales', 'edit sales', 'confirm sales',
            'view customers', 'create customers', 'edit customers',
            'view reports',
        ]);

        $supervisorRole = Role::firstOrCreate(
            ['name' => 'Supervisor', 'guard_name' => 'web'],
            ['description' => 'Supervisión de operaciones', 'color' => '#D97706', 'is_system' => true]
        );
        $supervisorRole->syncPermissions([
            'view dashboard', 'view products', 'view inventory', 'view kardex',
            'view recipes', 'view production', 'view purchases', 'view suppliers',
            'view sales', 'view customers', 'view reports', 'export reports',
            'view cost reports', 'view financial reports',
        ]);

        // Usuario administrador por defecto
        $admin = User::firstOrCreate(
            ['email' => 'admin@artisanerp.local'],
            [
                'name' => 'Administrador del Sistema',
                'username' => 'admin',
                'password' => Hash::make('Admin@ERP2024!'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $admin->assignRole($adminRole);

        // Usuario demo para cada rol
        $demoUsers = [
            ['name' => 'María González', 'email' => 'produccion@artisanerp.local', 'username' => 'produccion', 'role' => 'Producción'],
            ['name' => 'Carlos Mendoza', 'email' => 'compras@artisanerp.local', 'username' => 'compras', 'role' => 'Compras'],
            ['name' => 'Ana Rodríguez', 'email' => 'ventas@artisanerp.local', 'username' => 'ventas', 'role' => 'Ventas'],
            ['name' => 'Luis Torres', 'email' => 'supervisor@artisanerp.local', 'username' => 'supervisor', 'role' => 'Supervisor'],
        ];

        foreach ($demoUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'username' => $userData['username'],
                    'password' => Hash::make('Demo@ERP2024!'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );
            $user->assignRole($userData['role']);
        }
    }
}
