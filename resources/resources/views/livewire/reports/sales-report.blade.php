<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <a href="{{ route('reports.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reportes</a>
                <span class="text-gray-300">/</span>
                <span class="text-sm text-gray-700">Ventas</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Reporte de Ventas</h1>
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
            <div>
                <label class="form-label">Cliente</label>
                <select wire:model.live="customerId" class="form-input">
                    <option value="0">Todos los clientes</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Agrupar por</label>
                <select wire:model.live="groupBy" class="form-input">
                    <option value="day">Día</option>
                    <option value="week">Semana</option>
                    <option value="month">Mes</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Totals --}}
    @if($report['totals'])
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Total ventas</p>
            <p class="text-xl font-bold text-gray-900">{{ config('erp.currency_symbol') }} {{ number_format($report['totals']->total_sales ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Órdenes</p>
            <p class="text-xl font-bold text-gray-900">{{ $report['totals']->total_orders ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Utilidad bruta</p>
            <p class="text-xl font-bold text-emerald-600">{{ config('erp.currency_symbol') }} {{ number_format($report['totals']->total_profit ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Margen</p>
            <p class="text-xl font-bold text-indigo-600">{{ $report['totals']->margin_percent ?? 0 }}%</p>
        </div>
    </div>
    @endif

    {{-- Summary table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Ventas por período</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Período</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Órdenes</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Ventas</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Costo</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Utilidad</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Margen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($report['summary'] as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $row->period }}</td>
                        <td class="px-6 py-3 text-right text-sm text-gray-600">{{ $row->order_count }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono text-gray-900">{{ config('erp.currency_symbol') }} {{ number_format($row->total_sales, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono text-gray-500">{{ config('erp.currency_symbol') }} {{ number_format($row->total_cost, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono {{ $row->total_profit >= 0 ? 'text-emerald-600' : 'text-red-500' }}">{{ config('erp.currency_symbol') }} {{ number_format($row->total_profit, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm text-gray-600">{{ $row->margin_percent }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">No hay datos para el período seleccionado</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top products --}}
    @if($report['top_products']->count())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Top 10 productos más vendidos</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Cantidad</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Ingresos</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Margen</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($report['top_products'] as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $p->name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $p->sku }}</p>
                        </td>
                        <td class="px-6 py-3 text-right text-sm text-gray-600">{{ number_format($p->total_quantity, 2) }} {{ $p->unit }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono text-gray-900">{{ config('erp.currency_symbol') }} {{ number_format($p->total_revenue, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono {{ $p->total_margin >= 0 ? 'text-emerald-600' : 'text-red-500' }}">{{ config('erp.currency_symbol') }} {{ number_format($p->total_margin, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm text-gray-600">{{ $p->margin_percent }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
