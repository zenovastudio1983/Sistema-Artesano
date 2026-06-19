<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Órdenes de Compra</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $orders->total() }} órdenes en total</p>
        </div>
        @can('create purchase-orders')
        <a href="{{ route('purchases.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva Orden
        </a>
        @endcan
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Status summary chips --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <button wire:click="$set('filterStatus', '')"
                class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $filterStatus === '' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            Todas
        </button>
        @foreach($statuses as $status)
        @php
            $count = $statusCounts[$status->value] ?? 0;
            $chipClass = match($status->value) {
                'draft'     => $filterStatus === $status->value ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200',
                'sent'      => $filterStatus === $status->value ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100',
                'partial'   => $filterStatus === $status->value ? 'bg-yellow-500 text-white' : 'bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-100',
                'received'  => $filterStatus === $status->value ? 'bg-green-600 text-white' : 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100',
                'cancelled' => $filterStatus === $status->value ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100',
                default     => $filterStatus === $status->value ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50',
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
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Buscar por número de orden o proveedor..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <button wire:click="sort('order_number')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span># Orden</span>
                                @if($sortBy === 'order_number')
                                    <svg class="w-3 h-3 {{ $sortDir === 'desc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Proveedor</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <button wire:click="sort('created_at')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>Fecha</span>
                                @if($sortBy === 'created_at')
                                    <svg class="w-3 h-3 {{ $sortDir === 'desc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Almacén</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($orders as $order)
                    @php
                        $badgeClass = match($order->status->value) {
                            'draft'     => 'badge-gray',
                            'sent'      => 'badge-blue',
                            'partial'   => 'badge-yellow',
                            'received'  => 'badge-green',
                            'cancelled' => 'badge-red',
                            default     => 'badge-gray',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="text-sm font-mono font-medium text-indigo-600">{{ $order->order_number }}</p>
                            @if($order->createdBy)
                                <p class="text-xs text-gray-400">por {{ $order->createdBy->name }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-900">{{ $order->supplier?->display_name ?? '—' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <p>{{ $order->created_at->format('d/m/Y') }}</p>
                            @if($order->expected_date)
                                <p class="text-xs text-gray-400">Esperado: {{ \Carbon\Carbon::parse($order->expected_date)->format('d/m/Y') }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $order->warehouse?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-mono font-medium text-gray-900">
                                {{ config('erp.currency_symbol') }} {{ number_format($order->total, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="badge {{ $badgeClass }}">{{ $order->status->label() }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-1">
                                @can('view purchase-orders')
                                <a href="{{ route('purchases.show', $order) }}"
                                   class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @endcan
                                @can('edit purchase-orders')
                                @if($order->status->value === 'draft')
                                <a href="{{ route('purchases.edit', $order) }}"
                                   class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif
                                @endcan
                                @can('delete purchase-orders')
                                @if($order->status->value === 'draft')
                                <button wire:click="confirmDelete({{ $order->id }})"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm text-gray-500">No se encontraron órdenes de compra</p>
                            @if($search || $filterStatus)
                                <button wire:click="$set('search', ''); $set('filterStatus', '')" class="mt-1 text-xs text-indigo-600 hover:underline">Limpiar filtros</button>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $orders->links() }}</div>
        @endif
    </div>

    {{-- Delete modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500/75" wire:click="$set('showDeleteModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Eliminar orden</h3>
                    <p class="text-sm text-gray-500">Solo se pueden eliminar órdenes en borrador.</p>
                </div>
            </div>
            <div class="flex justify-end space-x-3">
                <button wire:click="$set('showDeleteModal', false)" class="btn-secondary">Cancelar</button>
                <button wire:click="delete" wire:loading.attr="disabled" class="btn-danger">
                    <span wire:loading.remove wire:target="delete">Eliminar</span>
                    <span wire:loading wire:target="delete">Eliminando...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
