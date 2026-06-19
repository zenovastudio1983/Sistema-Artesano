<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <a href="{{ route('reports.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reportes</a>
                <span class="text-gray-300">/</span>
                <span class="text-sm text-gray-700">Producción</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Reporte de Producción</h1>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-wrap gap-3">
            <div>
                <label class="form-label">Desde</label>
                <input wire:model.live="dateFrom" type="date" class="form-input">
            </div>
            <div>
                <label class="form-label">Hasta</label>
                <input wire:model.live="dateTo" type="date" class="form-input">
            </div>
        </div>
    </div>

    {{-- Totals --}}
    @if($report['totals'])
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Órdenes finalizadas</p>
            <p class="text-xl font-bold text-gray-900">{{ $report['totals']->total_orders ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Unidades producidas</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($report['totals']->total_units ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Costo total real</p>
            <p class="text-xl font-bold text-purple-700">{{ config('erp.currency_symbol') }} {{ number_format($report['totals']->total_cost ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Variación vs estimado</p>
            @php $variance = ($report['totals']->total_cost ?? 0) - ($report['totals']->estimated_cost ?? 0); @endphp
            <p class="text-xl font-bold {{ $variance > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                {{ $variance > 0 ? '+' : '' }}{{ config('erp.currency_symbol') }} {{ number_format($variance, 2) }}
            </p>
        </div>
    </div>
    @endif

    {{-- By product --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Costo por producto producido</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Producido</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Mat.</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">M.O.</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Overhead</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Costo unit.</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Variación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($report['orders'] as $row)
                    @php $var = ($row->total_cost ?? 0) - ($row->estimated_cost ?? 0); @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $row->name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $row->sku }}</p>
                        </td>
                        <td class="px-6 py-3 text-right text-sm text-gray-600">{{ number_format($row->total_produced, 2) }} {{ $row->unit }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono text-gray-700">{{ number_format($row->material_cost, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono text-gray-700">{{ number_format($row->labor_cost, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono text-gray-700">{{ number_format($row->overhead_cost, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono font-medium text-gray-900">{{ config('erp.currency_symbol') }} {{ number_format($row->total_cost, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono text-indigo-600">{{ config('erp.currency_symbol') }} {{ number_format($row->unit_cost, 4) }}</td>
                        <td class="px-6 py-3 text-right text-sm {{ $var > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                            {{ $var > 0 ? '+' : '' }}{{ number_format($var, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-6 py-8 text-center text-sm text-gray-400">No hay órdenes finalizadas en el período seleccionado</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
