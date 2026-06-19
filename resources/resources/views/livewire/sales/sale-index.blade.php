<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ventas</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $sales->total() }} ventas en total</p>
        </div>
        @can('create sales')
        <a href="{{ route('sales.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva Venta
        </a>
        @endcan
    </div>

    {{-- KPI bar --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Ventas confirmadas</p>
            <p class="text-lg font-bold text-gray-900">{{ config('erp.currency_symbol') }} {{ number_format($totals->confirmed_total ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Utilidad bruta</p>
            <p class="text-lg font-bold text-emerald-600">{{ config('erp.currency_symbol') }} {{ number_format($totals->total_profit ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Total de órdenes</p>
            <p class="text-lg font-bold text-gray-900">{{ $totals->total_orders ?? 0 }}</p>
        </div>
    </div>

    {{-- Status chips --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <button wire:click="$set('filterStatus', '')"
                class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $filterStatus === '' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            Todas
        </button>
        @foreach($statuses as $status)
        @php
            $count = $statusCounts[$status->value] ?? 0;
            $active = $filterStatus === $status->value;
            $chipClass = match($status->value) {
                'quotation' => $active ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 border border-gray-200',
                'confirmed' => $active ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 border border-blue-200',
                'invoiced'  => $active ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 border border-purple-200',
                'paid'      => $active ? 'bg-green-600 text-white' : 'bg-green-50 text-green-700 border border-green-200',
                'cancelled' => $active ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 border border-red-200',
                default     => $active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200',
            };
        @endphp
        <button wire:click="$set('filterStatus', '{{ $status->value }}')"
                class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $chipClass }}">
            {{ $status->label() }}
            @if($count > 0)<span class="ml-1 opacity-75">({{ $count }})</span>@endif
        </button>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Buscar por número, factura o cliente..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <input wire:model.live="dateFrom" type="date" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Desde">
            <input wire:model.live="dateTo" type="date" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Hasta">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"># Orden</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <button wire:click="sort('sale_date')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>Fecha</span>
                                @if($sortBy === 'sale_date')
                                    <svg class="w-3 h-3 {{ $sortDir === 'desc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Utilidad</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($sales as $sale)
                    @php
                        $badgeClass = match($sale->status->value) {
                            'quotation' => 'badge-gray',
                            'confirmed' => 'badge-blue',
                            'invoiced'  => 'badge-purple',
                            'paid'      => 'badge-green',
                            'cancelled' => 'badge-red',
                            default     => 'badge-gray',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="text-sm font-mono font-medium text-indigo-600">{{ $sale->order_number }}</p>
                            @if($sale->invoice_number)
                                <p class="text-xs text-gray-400 font-mono">{{ $sale->invoice_number }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-900">{{ $sale->customer?->display_name ?? 'Sin cliente' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-mono font-medium text-gray-900">
                                {{ config('erp.currency_symbol') }} {{ number_format($sale->total, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($sale->gross_profit > 0)
                                <span class="text-sm font-mono text-emerald-600">{{ config('erp.currency_symbol') }} {{ number_format($sale->gross_profit, 2) }}</span>
                            @elseif($sale->gross_profit < 0)
                                <span class="text-sm font-mono text-red-500">{{ config('erp.currency_symbol') }} {{ number_format($sale->gross_profit, 2) }}</span>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="badge {{ $badgeClass }}">{{ $sale->status->label() }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-1">
                                @can('view sales')
                                <a href="{{ route('sales.show', $sale) }}"
                                   class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @endcan
                                @can('edit sales')
                                @if(in_array($sale->status->value, ['quotation', 'confirmed']))
                                <a href="{{ route('sales.edit', $sale) }}"
                                   class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <p class="text-sm text-gray-500">No se encontraron ventas</p>
                            @if($search || $filterStatus || $dateFrom || $dateTo)
                                <button wire:click="$set('search', ''); $set('filterStatus', ''); $set('dateFrom', ''); $set('dateTo', '')"
                                        class="mt-1 text-xs text-indigo-600 hover:underline">Limpiar filtros</button>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $sales->links() }}</div>
        @endif
    </div>

</div>
