<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <a href="{{ route('reports.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reportes</a>
                <span class="text-gray-300">/</span>
                <span class="text-sm text-gray-700">Compras</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Reporte de Compras</h1>
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
                <label class="form-label">Proveedor</label>
                <select wire:model.live="supplierId" class="form-input">
                    <option value="0">Todos los proveedores</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->display_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Totals --}}
    @if($report['totals'])
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Total órdenes recibidas</p>
            <p class="text-2xl font-bold text-gray-900">{{ $report['totals']->total_orders ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500">Monto total</p>
            <p class="text-2xl font-bold text-orange-600">{{ config('erp.currency_symbol') }} {{ number_format($report['totals']->total_amount ?? 0, 2) }}</p>
        </div>
    </div>
    @endif

    {{-- By supplier --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Compras por proveedor</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Proveedor</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Órdenes</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">% del total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php $grandTotal = $report['totals']->total_amount ?? 1; @endphp
                    @forelse($report['by_supplier'] as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $row->trade_name ?? $row->business_name }}</p>
                        </td>
                        <td class="px-6 py-3 text-right text-sm text-gray-600">{{ $row->order_count }}</td>
                        <td class="px-6 py-3 text-right text-sm font-mono text-gray-900">{{ config('erp.currency_symbol') }} {{ number_format($row->total_amount, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm text-gray-500">
                            {{ $grandTotal > 0 ? number_format($row->total_amount / $grandTotal * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-400">No hay datos para el período seleccionado</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
