<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center space-x-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('inventory.index') }}" class="hover:text-indigo-600">Inventario</a>
                <span>/</span>
                <span>Kardex</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Kardex — {{ $product->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">SKU: {{ $product->sku }} | Unidad: {{ $product->unit }}</p>
        </div>
        @if($currentInventory)
        <div class="text-right">
            <p class="text-sm text-gray-500">Stock actual</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($currentInventory->quantity, 4) }} {{ $product->unit }}</p>
            <p class="text-sm text-gray-500">Costo prom: {{ config('erp.currency_symbol') }} {{ number_format($currentInventory->average_cost, 4) }}</p>
        </div>
        @endif
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-wrap gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Almacén</label>
                <select wire:model.live="warehouseId" class="py-2 px-3 border border-gray-200 rounded-lg text-sm">
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Desde</label>
                <input wire:model.live="dateFrom" type="date"
                       class="py-2 px-3 border border-gray-200 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Hasta</label>
                <input wire:model.live="dateTo" type="date"
                       class="py-2 px-3 border border-gray-200 rounded-lg text-sm">
            </div>
        </div>
    </div>

    {{-- Kardex table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Movimientos del período</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Referencia</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase text-green-700">Entradas</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase text-red-700">Salidas</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Costo Unit.</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Saldo Cant.</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Costo Prom.</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Valor Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($movements as $movement)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-600">{{ $movement->moved_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 font-mono text-gray-600 text-xs">{{ $movement->reference_number ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $movement->movement_type->isEntry() ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                {{ $movement->movement_type->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-mono">
                            @if($movement->movement_type->isEntry())
                                <span class="text-green-700 font-semibold">{{ number_format($movement->quantity, 4) }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-mono">
                            @if($movement->movement_type->isExit())
                                <span class="text-red-600 font-semibold">{{ number_format($movement->quantity, 4) }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-700">
                            {{ config('erp.currency_symbol') }} {{ number_format($movement->unit_cost, 4) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-semibold text-gray-900">
                            {{ number_format($movement->balance_quantity, 4) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-700">
                            {{ config('erp.currency_symbol') }} {{ number_format($movement->balance_average_cost, 4) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-900">
                            {{ config('erp.currency_symbol') }} {{ number_format($movement->balance_total_value, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                            <svg class="mx-auto w-10 h-10 text-gray-200 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            No hay movimientos en el período seleccionado
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($movements->isNotEmpty())
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase">Totales del período:</td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-green-700">
                            {{ number_format($movements->where('movement_type', fn($t) => $t->isEntry())->sum('quantity'), 4) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-red-600">
                            {{ number_format($movements->where('movement_type', fn($t) => $t->isExit())->sum('quantity'), 4) }}
                        </td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
