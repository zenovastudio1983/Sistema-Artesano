<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Inventario</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $inventory->total() }} productos activos</p>
        </div>
        <div class="flex items-center gap-2">
            @can('adjust inventory')
            <a href="{{ route('inventory.adjust') }}"
               class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Ajustar
            </a>
            <a href="{{ route('inventory.transfer') }}"
               class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Transferir
            </a>
            @endcan
            <a href="{{ route('inventory.movements') }}"
               class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Movimientos
            </a>
        </div>
    </div>

    {{-- Summary KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 col-span-2 sm:col-span-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Productos</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary->total_products) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 col-span-2 sm:col-span-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Total</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
                {{ config('erp.currency_symbol') }} {{ number_format($summary->total_value ?? 0, 0) }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-red-100 p-4">
            <p class="text-xs font-medium text-red-500 uppercase tracking-wider">Sin Stock</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $summary->out_of_stock }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-orange-100 p-4">
            <p class="text-xs font-medium text-orange-500 uppercase tracking-wider">Crítico</p>
            <p class="text-2xl font-bold text-orange-600 mt-1">{{ $summary->critical }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-yellow-100 p-4">
            <p class="text-xs font-medium text-yellow-600 uppercase tracking-wider">Stock Bajo</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $summary->low }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search"
                       type="text"
                       placeholder="Buscar por nombre o SKU..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <select wire:model.live="filterStatus" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los estados</option>
                <option value="ok">Normal</option>
                <option value="low">Stock bajo</option>
                <option value="critical">Crítico</option>
                <option value="out_of_stock">Sin stock</option>
            </select>
            <select wire:model.live="filterType" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los tipos</option>
                <option value="raw_material">Materia Prima</option>
                <option value="finished_product">Producto Terminado</option>
                <option value="semi_finished">Semi-elaborado</option>
                <option value="supply">Insumo</option>
            </select>
            @if($warehouses->count() > 1)
            <select wire:model.live="filterWarehouse" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="0">Todos los almacenes</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <button wire:click="$set('sortBy', 'product_name')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>Producto</span>
                                @if($sortBy === 'product_name')
                                    <svg class="w-3 h-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo / Categoría</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <button wire:click="$set('sortBy', 'total_stock')" class="flex items-center space-x-1 hover:text-gray-700 ml-auto">
                                <span>Stock Total</span>
                                @if($sortBy === 'total_stock')
                                    <svg class="w-3 h-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Reservado</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Disponible</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <button wire:click="$set('sortBy', 'total_inventory_value')" class="flex items-center space-x-1 hover:text-gray-700 ml-auto">
                                <span>Valor</span>
                                @if($sortBy === 'total_inventory_value')
                                    <svg class="w-3 h-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($inventory as $item)
                    @php
                        $invBadgeClass = match($item->product_type) {
                            'finished_product' => 'bg-purple-50 text-purple-700',
                            'raw_material'     => 'bg-blue-50 text-blue-700',
                            'supply'           => 'bg-amber-50 text-amber-700',
                            'semi_finished'    => 'bg-gray-100 text-gray-600',
                            default            => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $item->product_name }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $item->sku }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invBadgeClass }}">
                                {{ match($item->product_type) {
                                    'raw_material' => 'Materia Prima',
                                    'finished_product' => 'Prod. Terminado',
                                    'semi_finished' => 'Semi-elaborado',
                                    'supply' => 'Insumo',
                                    default => $item->product_type
                                } }}
                            </span>
                            @if($item->category_name)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $item->category_name }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-mono font-medium
                                {{ $item->stock_status === 'out_of_stock' ? 'text-red-600' :
                                   ($item->stock_status === 'critical' ? 'text-orange-600' :
                                   ($item->stock_status === 'low' ? 'text-yellow-600' : 'text-gray-900')) }}">
                                {{ number_format($item->total_stock, 2) }}
                            </span>
                            <span class="text-xs text-gray-400 ml-1">{{ $item->unit }}</span>
                            @if($item->stock_minimum > 0)
                                <p class="text-xs text-gray-400">Mín: {{ number_format($item->stock_minimum, 2) }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($item->total_reserved > 0)
                                <span class="text-sm font-mono text-orange-600">{{ number_format($item->total_reserved, 2) }}</span>
                                <span class="text-xs text-gray-400 ml-1">{{ $item->unit }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-mono text-gray-900">{{ number_format($item->available_stock, 2) }}</span>
                            <span class="text-xs text-gray-400 ml-1">{{ $item->unit }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-mono text-gray-700">
                                {{ config('erp.currency_symbol') }} {{ number_format($item->total_inventory_value, 2) }}
                            </span>
                            <p class="text-xs text-gray-400">
                                @ {{ config('erp.currency_symbol') }} {{ number_format($item->current_cost, 4) }}
                            </p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusConfig = match($item->stock_status) {
                                    'out_of_stock' => ['label' => 'Sin stock', 'class' => 'bg-red-100 text-red-800'],
                                    'critical'     => ['label' => 'Crítico',   'class' => 'bg-orange-100 text-orange-800'],
                                    'low'          => ['label' => 'Stock bajo','class' => 'bg-yellow-100 text-yellow-800'],
                                    default        => ['label' => 'Normal',    'class' => 'bg-green-100 text-green-800'],
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusConfig['class'] }}">
                                {{ $statusConfig['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-1">
                                <a href="{{ route('inventory.kardex', $item->product_id) }}"
                                   title="Ver Kardex"
                                   class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                </a>
                                @can('adjust inventory')
                                <a href="{{ route('inventory.adjust') }}?product={{ $item->product_id }}"
                                   title="Ajustar stock"
                                   class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No se encontraron productos en inventario</p>
                            @if($search)
                                <button wire:click="$set('search', '')" class="mt-1 text-xs text-indigo-600 hover:underline">
                                    Limpiar búsqueda
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($inventory->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $inventory->links() }}
        </div>
        @endif
    </div>

</div>
