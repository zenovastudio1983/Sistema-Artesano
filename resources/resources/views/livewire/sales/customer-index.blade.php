<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Clientes</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $customers->total() }} clientes registrados</p>
        </div>
        @can('create customers')
        <a href="{{ route('customers.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Cliente
        </a>
        @endcan
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Buscar por nombre, RUC, código..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <select wire:model.live="filterType" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los tipos</option>
                <option value="retail">Minorista</option>
                <option value="wholesale">Mayorista</option>
                <option value="distributor">Distribuidor</option>
            </select>
            <select wire:model.live="filterStatus" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los estados</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <button wire:click="sort('business_name')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>Cliente</span>
                                @if($sortBy === 'business_name')
                                    <svg class="w-3 h-3 {{ $sortDir === 'desc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contacto</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Saldo</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Desc.</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Ventas</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($customers as $customer)
                    @php
                        $typeClass = match($customer->customer_type) {
                            'wholesale' => 'bg-purple-50 text-purple-700',
                            'distributor' => 'bg-blue-50 text-blue-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                        $typeLabel = match($customer->customer_type) {
                            'retail' => 'Minorista',
                            'wholesale' => 'Mayorista',
                            'distributor' => 'Distribuidor',
                            default => 'General',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 bg-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-emerald-700">{{ strtoupper(substr($customer->display_name, 0, 2)) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $customer->display_name }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $customer->tax_id ?? $customer->code ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeClass }}">{{ $typeLabel }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-700">{{ $customer->contact_name ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ $customer->email ?? $customer->phone ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-mono {{ $customer->current_balance > 0 ? 'text-orange-600' : 'text-gray-700' }}">
                                {{ config('erp.currency_symbol') }} {{ number_format($customer->current_balance, 2) }}
                            </span>
                            @if($customer->credit_limit > 0)
                                <p class="text-xs text-gray-400">Límite: {{ number_format($customer->credit_limit, 0) }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-gray-700">
                            {{ $customer->discount_percent > 0 ? number_format($customer->discount_percent, 1) . '%' : '—' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-medium text-gray-700">{{ $customer->sales_count }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="badge {{ $customer->is_active ? 'badge-green' : 'badge-gray' }}">
                                {{ $customer->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-1">
                                @can('edit customers')
                                <a href="{{ route('customers.edit', $customer) }}"
                                   class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endcan
                                @can('delete customers')
                                <button wire:click="confirmDelete({{ $customer->id }})"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-sm text-gray-500">No se encontraron clientes</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $customers->links() }}</div>
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
                    <h3 class="text-lg font-semibold text-gray-900">Eliminar cliente</h3>
                    <p class="text-sm text-gray-500">Esta acción no se puede deshacer.</p>
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
