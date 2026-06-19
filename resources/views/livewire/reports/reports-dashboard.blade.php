<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Reportes</h1>
        <p class="text-sm text-gray-500 mt-1">Resumen ejecutivo y análisis de negocio</p>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-gradient-to-br from-emerald-500 to-green-700 rounded-xl p-5 text-white">
            <p class="text-xs text-green-100 mb-1">Ventas este mes</p>
            <p class="text-2xl font-bold">{{ config('erp.currency_symbol') }} {{ number_format($kpis['monthly_sales'], 0) }}</p>
            @if($kpis['sales_growth'] != 0)
                <p class="text-xs mt-1 {{ $kpis['sales_growth'] > 0 ? 'text-green-200' : 'text-red-300' }}">
                    {{ $kpis['sales_growth'] > 0 ? '+' : '' }}{{ $kpis['sales_growth'] }}% vs mes anterior
                </p>
            @endif
        </div>
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-900 rounded-xl p-5 text-white">
            <p class="text-xs text-indigo-200 mb-1">Utilidad bruta mes</p>
            <p class="text-2xl font-bold">{{ config('erp.currency_symbol') }} {{ number_format($kpis['monthly_gross_profit'], 0) }}</p>
            <p class="text-xs text-indigo-200 mt-1">Margen: {{ $kpis['gross_margin_percent'] }}%</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 mb-1">Valor del inventario</p>
            <p class="text-xl font-bold text-amber-700">{{ config('erp.currency_symbol') }} {{ number_format($kpis['total_inventory_value'], 0) }}</p>
            @if($kpis['critical_stock_count'] > 0)
                <p class="text-xs text-red-500 mt-1">{{ $kpis['critical_stock_count'] }} productos críticos</p>
            @endif
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 mb-1">Compras pendientes</p>
            <p class="text-xl font-bold text-gray-900">{{ $kpis['pending_purchases_count'] }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ config('erp.currency_symbol') }} {{ number_format($kpis['pending_purchases_amount'], 0) }} en total</p>
        </div>
    </div>

    {{-- Report links --}}
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Reportes disponibles</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        <a href="{{ route('reports.sales') }}" class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-indigo-200 transition-all">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Ventas</h3>
                    <p class="text-xs text-gray-400">Análisis por período y cliente</p>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.purchases') }}" class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-indigo-200 transition-all">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                    <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Compras</h3>
                    <p class="text-xs text-gray-400">Órdenes recibidas por proveedor</p>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.inventory') }}" class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-indigo-200 transition-all">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Inventario</h3>
                    <p class="text-xs text-gray-400">Stock actual y valorización</p>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.production') }}" class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-indigo-200 transition-all">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Producción</h3>
                    <p class="text-xs text-gray-400">Costos y eficiencia productiva</p>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.profitability') }}" class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-indigo-200 transition-all">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Rentabilidad</h3>
                    <p class="text-xs text-gray-400">Márgenes por producto</p>
                </div>
            </div>
        </a>

    </div>

</div>
