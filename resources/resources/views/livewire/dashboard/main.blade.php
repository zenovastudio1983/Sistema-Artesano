<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Ejecutivo</h1>
            <p class="text-sm text-gray-500 mt-1">Bienvenido, {{ auth()->user()->name }} — {{ now()->format('d M Y') }}</p>
        </div>
        <button wire:click="refreshKpis" wire:loading.attr="disabled"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <svg wire:loading class="animate-spin -ml-0.5 mr-2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <svg wire:loading.remove class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Actualizar
        </button>
    </div>

    {{-- KPI Cards Row 1 --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        {{-- Ventas del mes --}}
        <div class="bg-gradient-to-br from-emerald-500 to-green-700 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="inline-flex items-center text-xs font-medium text-white bg-white/20 rounded-full px-2 py-0.5">
                    {{ $kpis['sales_growth'] >= 0 ? '▲' : '▼' }} {{ abs($kpis['sales_growth']) }}%
                </span>
            </div>
            <p class="text-2xl font-bold text-white">
                {{ config('erp.currency_symbol') }} {{ number_format($kpis['monthly_sales'], 2) }}
            </p>
            <p class="text-sm text-green-100 mt-1">Ventas del mes</p>
            <p class="text-xs text-green-200 mt-1">vs. {{ config('erp.currency_symbol') }} {{ number_format($kpis['last_month_sales'], 2) }} mes anterior</p>
        </div>

        {{-- Margen Bruto --}}
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-900 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-white bg-white/20 rounded-full px-2 py-0.5">
                    {{ $kpis['gross_margin_percent'] }}%
                </span>
            </div>
            <p class="text-2xl font-bold text-white">
                {{ config('erp.currency_symbol') }} {{ number_format($kpis['monthly_gross_profit'], 2) }}
            </p>
            <p class="text-sm text-indigo-200 mt-1">Margen bruto del mes</p>
            <div class="mt-2 w-full bg-white/20 rounded-full h-1.5">
                <div class="h-1.5 rounded-full bg-white/70"
                     style="width: {{ min(100, $kpis['gross_margin_percent']) }}%"></div>
            </div>
        </div>

        {{-- Producción del mes --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-purple-50 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="inline-flex items-center text-xs font-medium text-purple-700 bg-purple-50 rounded-full px-2 py-0.5">
                    {{ $kpis['active_production_orders'] }} activas
                </span>
            </div>
            <p class="text-2xl font-bold text-gray-900">
                {{ number_format($kpis['monthly_produced_units']) }} <span class="text-sm font-normal text-gray-400">und</span>
            </p>
            <p class="text-sm text-gray-500 mt-1">Producción del mes</p>
            <p class="text-xs text-gray-400 mt-1">Costo: {{ config('erp.currency_symbol') }} {{ number_format($kpis['monthly_production_cost'], 2) }}</p>
        </div>

        {{-- Alertas de Stock --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 {{ $kpis['critical_stock_count'] > 0 ? 'bg-red-50' : 'bg-gray-50' }} rounded-lg">
                    <svg class="w-6 h-6 {{ $kpis['critical_stock_count'] > 0 ? 'text-red-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                @if($kpis['critical_stock_count'] > 0)
                    <span class="inline-flex items-center text-xs font-medium text-red-700 bg-red-50 rounded-full px-2 py-0.5">
                        {{ $kpis['critical_stock_count'] }} críticos
                    </span>
                @endif
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $kpis['critical_stock_count'] + $kpis['low_stock_count'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Alertas de stock</p>
            <a href="{{ route('inventory.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 mt-1 inline-block">
                Ver inventario →
            </a>
        </div>
    </div>

    {{-- Row 2: Compras pendientes + Valor inventario --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Compras pendientes --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Compras Pendientes
            </h3>
            <p class="text-3xl font-bold text-gray-900">{{ $kpis['pending_purchases_count'] }}</p>
            <p class="text-sm text-gray-500 mt-1">órdenes pendientes</p>
            <p class="text-sm font-medium text-gray-700 mt-2">
                {{ config('erp.currency_symbol') }} {{ number_format($kpis['pending_purchases_amount'], 2) }}
            </p>
            <a href="{{ route('purchases.index') }}" class="mt-3 text-xs text-indigo-600 hover:text-indigo-800 inline-block">
                Gestionar compras →
            </a>
        </div>

        {{-- Valor del inventario --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-amber-400 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                <svg class="w-4 h-4 mr-2 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Valor del Inventario
            </h3>
            <p class="text-3xl font-bold text-amber-700">
                {{ config('erp.currency_symbol') }} {{ number_format($kpis['total_inventory_value'], 0) }}
            </p>
            <p class="text-sm text-gray-500 mt-1">valor total en almacén</p>
            <a href="{{ route('reports.inventory') }}" class="mt-3 text-xs text-indigo-600 hover:text-indigo-800 inline-block">
                Ver reporte →
            </a>
        </div>

        {{-- Rentabilidad anual --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                Rentabilidad {{ now()->year }}
            </h3>
            <p class="text-3xl font-bold text-gray-900">
                {{ config('erp.currency_symbol') }} {{ number_format($kpis['yearly_gross_profit'], 0) }}
            </p>
            <p class="text-sm text-gray-500 mt-1">utilidad bruta del año</p>
            <a href="{{ route('reports.profitability') }}" class="mt-3 text-xs text-indigo-600 hover:text-indigo-800 inline-block">
                Ver análisis →
            </a>
        </div>
    </div>

    {{-- Row 3: Stock crítico --}}
    @if(count($lowStockProducts) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-red-100 mb-8">
        <div class="px-6 py-4 border-b border-red-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-red-700 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                Productos con Stock Crítico
            </h3>
            <a href="{{ route('inventory.index') }}" class="text-xs text-red-600 hover:text-red-800">Ver todos</a>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($lowStockProducts as $item)
            <div class="px-6 py-3 flex items-center justify-between hover:bg-red-50 transition-colors">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <span class="text-xs font-bold text-red-700">{{ strtoupper(substr($item['name'], 0, 2)) }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $item['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $item['sku'] }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-red-600">{{ number_format($item['total_stock'] ?? 0, 2) }} {{ $item['unit'] }}</p>
                    <p class="text-xs text-gray-400">Mín: {{ number_format($item['stock_minimum'], 2) }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
