<aside class="hidden lg:flex lg:flex-col w-64 bg-gray-900 flex-shrink-0">
    {{-- Logo --}}
    <div class="flex items-center h-16 px-6 bg-gray-950 flex-shrink-0">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-white font-semibold text-sm truncate leading-none">{{ config('erp.company.name', 'Artisan ERP') }}</p>
                <p class="text-gray-500 text-xs truncate leading-none mt-0.5">Gestión artesanal</p>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        @php
            $navItems = [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'permission' => 'view dashboard'],
                ['section' => 'Productos'],
                ['route' => 'products.index', 'label' => 'Productos', 'icon' => 'cube', 'permission' => 'view products'],
                ['route' => 'categories.index', 'label' => 'Categorías', 'icon' => 'tag', 'permission' => 'view categories'],
                ['section' => 'Inventario'],
                ['route' => 'inventory.index', 'label' => 'Inventario', 'icon' => 'archive', 'permission' => 'view inventory'],
                ['route' => 'warehouses.index', 'label' => 'Almacenes', 'icon' => 'office-building', 'permission' => 'view inventory'],
                ['route' => 'inventory.movements', 'label' => 'Movimientos', 'icon' => 'arrows-expand', 'permission' => 'view inventory'],
                ['section' => 'Producción'],
                ['route' => 'recipes.index', 'label' => 'Recetas', 'icon' => 'clipboard-list', 'permission' => 'view recipes'],
                ['route' => 'production.index', 'label' => 'Órdenes de Producción', 'icon' => 'cog', 'permission' => 'view production'],
                ['section' => 'Compras'],
                ['route' => 'purchases.index', 'label' => 'Órdenes de Compra', 'icon' => 'shopping-cart', 'permission' => 'view purchases'],
                ['route' => 'suppliers.index', 'label' => 'Proveedores', 'icon' => 'truck', 'permission' => 'view suppliers'],
                ['section' => 'Ventas'],
                ['route' => 'sales.index', 'label' => 'Ventas', 'icon' => 'currency-dollar', 'permission' => 'view sales'],
                ['route' => 'customers.index', 'label' => 'Clientes', 'icon' => 'users', 'permission' => 'view customers'],
                ['section' => 'Análisis'],
                ['route' => 'reports.index', 'label' => 'Reportes', 'icon' => 'chart-bar', 'permission' => 'view reports'],
                ['section' => 'Administración'],
                ['route' => 'users.index', 'label' => 'Usuarios', 'icon' => 'user-group', 'permission' => 'view users'],
                ['route' => 'settings.index', 'label' => 'Configuración', 'icon' => 'adjustments', 'permission' => 'view settings'],
            ];
        @endphp

        @foreach($navItems as $item)
            @if(isset($item['section']))
                <div class="pt-3 pb-1 px-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $item['section'] }}</p>
                </div>
            @else
                @can($item['permission'])
                <a href="{{ route($item['route']) }}"
                   class="group flex items-center py-2 text-sm font-medium transition-colors duration-150
                          {{ request()->routeIs(rtrim($item['route'], '.index') . '*')
                              ? 'bg-indigo-500/20 text-white border-l-2 border-indigo-400 pl-[10px] pr-3 rounded-r-md'
                              : 'text-gray-400 hover:bg-gray-700/60 hover:text-white px-3 rounded-md' }}">
                    @include('components.icon', ['name' => $item['icon'], 'class' => 'mr-3 h-5 w-5 flex-shrink-0'])
                    {{ $item['label'] }}
                </a>
                @endcan
            @endif
        @endforeach
    </nav>

    {{-- User info --}}
    <div class="flex-shrink-0 p-4 border-t border-gray-700">
        <div class="flex items-center space-x-3">
            <img src="{{ auth()->user()->avatar_url }}"
                 alt="{{ auth()->user()->name }}"
                 class="w-8 h-8 rounded-full">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->getRoleNames()->first() }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
