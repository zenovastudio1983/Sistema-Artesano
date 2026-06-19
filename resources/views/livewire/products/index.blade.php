<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Productos</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $products->total() }} productos registrados</p>
        </div>
        @can('create products')
        <a href="{{ route('products.create') }}"
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Producto
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
                <input wire:model.live.debounce.300ms="search"
                       type="text"
                       placeholder="Buscar por nombre, SKU o código de barras..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <select wire:model.live="filterType" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los tipos</option>
                @foreach($types as $type)
                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los estados</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterCategory" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="0">Todas las categorías</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->full_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <button wire:click="sort('sku')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>SKU / Nombre</span>
                                @if($sortBy === 'sku')
                                    <svg class="w-3 h-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Categoría</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <button wire:click="sort('cost')" class="flex items-center space-x-1 hover:text-gray-700 ml-auto">
                                <span>Costo</span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Precio</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($products as $product)
                    @php
                        $typeValue = $product->type->value ?? '';
                        $avatarClass = match($typeValue) {
                            'finished_product' => 'bg-purple-100 text-purple-700',
                            'raw_material'     => 'bg-blue-100 text-blue-700',
                            'supply'           => 'bg-amber-100 text-amber-700',
                            'semi_finished'    => 'bg-gray-100 text-gray-600',
                            default            => 'bg-indigo-100 text-indigo-700',
                        };
                        $badgeClass = match($typeValue) {
                            'finished_product' => 'bg-purple-50 text-purple-700',
                            'raw_material'     => 'bg-blue-50 text-blue-700',
                            'supply'           => 'bg-amber-50 text-amber-700',
                            'semi_finished'    => 'bg-gray-100 text-gray-600',
                            default            => 'bg-indigo-50 text-indigo-700',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                         class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                                @else
                                    <div class="w-10 h-10 {{ $avatarClass }} rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('products.show', $product) }}"
                                       class="text-sm font-medium text-gray-900 hover:text-indigo-600 transition-colors">
                                        {{ $product->name }}
                                    </a>
                                    <p class="text-xs text-gray-400 font-mono">{{ $product->sku }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                {{ $product->type->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-mono text-gray-700">
                                {{ config('erp.currency_symbol') }} {{ number_format($product->average_cost, 4) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($product->price > 0)
                                <span class="text-sm font-mono text-gray-900 font-medium">
                                    {{ config('erp.currency_symbol') }} {{ number_format($product->price, 2) }}
                                </span>
                                <p class="text-xs text-gray-400">{{ $product->margin }}% margen</p>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @php
                                $stock = $product->total_stock;
                                $isLow = $product->isLowStock();
                                $isOut = $product->isOutOfStock();
                            @endphp
                            <div class="text-right">
                                <span class="text-sm font-mono font-medium {{ $isOut ? 'text-red-600' : ($isLow ? 'text-yellow-600' : 'text-gray-900') }}">
                                    {{ number_format($stock, 2) }}
                                </span>
                                <span class="text-xs text-gray-400 ml-1">{{ $product->unit }}</span>
                                @if($isOut)
                                    <div class="text-xs text-red-500 font-medium">Sin stock</div>
                                @elseif($isLow)
                                    <div class="text-xs text-yellow-600">Stock bajo</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $product->status === \App\Support\Enums\ProductStatus::Active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $product->status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('inventory.kardex', $product) }}"
                                   title="Kardex"
                                   class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </a>
                                @can('edit products')
                                <a href="{{ route('products.edit', $product) }}"
                                   title="Editar"
                                   class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endcan
                                @can('delete products')
                                <button wire:click="confirmDelete({{ $product->id }})"
                                        title="Eliminar"
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
                            <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No se encontraron productos</p>
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
        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    {{-- Delete confirmation modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showDeleteModal', false)"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Eliminar producto</h3>
                        <p class="text-sm text-gray-500">Esta acción no se puede deshacer.</p>
                    </div>
                </div>
                <div class="flex space-x-3 justify-end">
                    <button wire:click="$set('showDeleteModal', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button wire:click="delete"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 disabled:opacity-50">
                        <span wire:loading wire:target="delete">Eliminando...</span>
                        <span wire:loading.remove wire:target="delete">Sí, eliminar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
