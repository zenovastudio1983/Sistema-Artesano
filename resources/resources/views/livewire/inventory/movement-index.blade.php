<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Movimientos de Inventario</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $movements->total() }} movimientos</p>
        </div>
        @can('create stock-movements')
        <a href="{{ route('inventory.adjust') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajuste Manual
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Buscar por producto o SKU..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <select wire:model.live="filterType" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los tipos</option>
                @foreach($movementTypes as $type)
                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterWarehouse" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="0">Todos los almacenes</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
            </select>
            <input wire:model.live="dateFrom" type="date" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            <input wire:model.live="dateTo" type="date" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Almacén</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Cantidad</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Costo unit.</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Referencia</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Creado por</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($movements as $movement)
                    @php
                        $typeClass = match($movement->movement_type->value ?? $movement->movement_type) {
                            'purchase_receipt', 'production_output', 'adjustment_in', 'transfer_in' => 'badge-green',
                            'sale_delivery', 'production_consumption', 'adjustment_out', 'transfer_out' => 'badge-red',
                            'adjustment' => 'badge-yellow',
                            default => 'badge-gray',
                        };
                        $qtyClass = in_array($movement->movement_type->value ?? $movement->movement_type, [
                            'purchase_receipt', 'production_output', 'adjustment_in', 'transfer_in'
                        ]) ? 'text-emerald-600' : 'text-red-500';
                        $qtySign = in_array($movement->movement_type->value ?? $movement->movement_type, [
                            'purchase_receipt', 'production_output', 'adjustment_in', 'transfer_in'
                        ]) ? '+' : '-';
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <p>{{ $movement->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $movement->created_at->format('H:i') }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-900">{{ $movement->product?->name ?? '—' }}</p>
                            @if($movement->product?->sku)
                                <p class="text-xs text-gray-400 font-mono">{{ $movement->product->sku }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge {{ $typeClass }}">{{ $movement->movement_type->label() }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $movement->warehouse?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-mono font-medium {{ $qtyClass }}">
                                {{ $qtySign }}{{ number_format(abs($movement->quantity), 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-mono text-gray-700">
                            {{ $movement->unit_cost ? config('erp.currency_symbol') . ' ' . number_format($movement->unit_cost, 4) : '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $movement->reference ?? $movement->notes ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $movement->createdBy?->name ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                            </svg>
                            <p class="text-sm text-gray-500">No se encontraron movimientos</p>
                            @if($search || $filterType || $filterWarehouse || $dateFrom || $dateTo)
                                <button wire:click="$set('search',''); $set('filterType',''); $set('filterWarehouse',0); $set('dateFrom',''); $set('dateTo','')"
                                        class="mt-1 text-xs text-indigo-600 hover:underline">Limpiar filtros</button>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $movements->links() }}</div>
        @endif
    </div>

</div>
