<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <a href="{{ route('reports.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reportes</a>
                <span class="text-gray-300">/</span>
                <span class="text-sm text-gray-700">Inventario</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Reporte de Inventario</h1>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Productos totales</p>
            <p class="text-xl font-bold text-gray-900">{{ $summary['total_products'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Valor total</p>
            <p class="text-xl font-bold text-amber-700">{{ config('erp.currency_symbol') }} {{ number_format($summary['total_value'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-red-100 shadow-sm p-4">
            <p class="text-xs text-red-500">Críticos / Sin stock</p>
            <p class="text-xl font-bold text-red-600">{{ $summary['critical_count'] + $summary['out_of_stock_count'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-yellow-100 shadow-sm p-4">
            <p class="text-xs text-yellow-600">Stock bajo</p>
            <p class="text-xl font-bold text-yellow-600">{{ $summary['low_count'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar producto..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <select wire:model.live="filterStatus" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los estados</option>
                <option value="ok">OK</option>
                <option value="low">Bajo</option>
                <option value="critical">Crítico</option>
                <option value="out_of_stock">Sin stock</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Categoría</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Stock</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Mínimo</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Costo prom.</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Valor total</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($items as $item)
                    @php
                        $statusClass = match($item->stock_status) {
                            'ok' => 'badge-green',
                            'low' => 'badge-yellow',
                            'critical' => 'badge-orange',
                            'out_of_stock' => 'badge-red',
                            default => 'badge-gray',
                        };
                        $statusLabel = match($item->stock_status) {
                            'ok' => 'OK',
                            'low' => 'Bajo',
                            'critical' => 'Crítico',
                            'out_of_stock' => 'Sin stock',
                            default => $item->stock_status,
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $item->sku }}</p>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $item->category_name ?? '—' }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono text-gray-900">{{ number_format($item->total_stock, 2) }} {{ $item->unit }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono text-gray-500">{{ number_format($item->stock_minimum ?? 0, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono text-gray-700">{{ config('erp.currency_symbol') }} {{ number_format($item->average_cost ?? 0, 4) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono font-medium text-gray-900">{{ config('erp.currency_symbol') }} {{ number_format($item->total_inventory_value ?? 0, 2) }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-sm text-gray-400">No se encontraron productos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
