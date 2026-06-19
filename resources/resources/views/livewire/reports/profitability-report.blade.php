<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <a href="{{ route('reports.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reportes</a>
                <span class="text-gray-300">/</span>
                <span class="text-sm text-gray-700">Rentabilidad</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Reporte de Rentabilidad</h1>
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

    {{-- Summary --}}
    @if($salesData['totals'])
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Ingresos totales</p>
            <p class="text-xl font-bold text-gray-900">{{ config('erp.currency_symbol') }} {{ number_format($salesData['totals']->total_sales ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Utilidad bruta</p>
            <p class="text-xl font-bold text-emerald-600">{{ config('erp.currency_symbol') }} {{ number_format($salesData['totals']->total_profit ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Margen bruto</p>
            <p class="text-xl font-bold text-indigo-600">{{ $salesData['totals']->margin_percent ?? 0 }}%</p>
        </div>
    </div>
    @endif

    {{-- By product --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Rentabilidad por producto</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Cant. vendida</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Ingresos</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Utilidad</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Margen %</th>
                        <th class="px-6 py-3" style="width:150px"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php $maxRevenue = $byProduct->max('total_revenue') ?: 1; @endphp
                    @forelse($byProduct as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $p->name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $p->sku }}</p>
                        </td>
                        <td class="px-6 py-3 text-right text-sm text-gray-600">{{ number_format($p->total_qty, 2) }} {{ $p->unit }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono text-gray-900">{{ config('erp.currency_symbol') }} {{ number_format($p->total_revenue, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono {{ $p->total_margin >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ config('erp.currency_symbol') }} {{ number_format($p->total_margin, 2) }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            <span class="text-sm font-medium {{ $p->margin_pct >= 30 ? 'text-emerald-600' : ($p->margin_pct >= 15 ? 'text-yellow-600' : 'text-red-500') }}">
                                {{ $p->margin_pct }}%
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full {{ $p->margin_pct >= 30 ? 'bg-emerald-500' : ($p->margin_pct >= 15 ? 'bg-yellow-400' : 'bg-red-400') }}"
                                     style="width: {{ min(100, max(0, $p->margin_pct)) }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">No hay datos de ventas para el período seleccionado</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
