<div>
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('products.index') }}" class="hover:text-gray-700">Productos</a>
                <span>/</span>
                <span>{{ $product->name }}</span>
            </div>
            <h1 class="page-title">{{ $product->name }}</h1>
            @if($product->sku)
                <p class="text-sm text-gray-500 mt-0.5">SKU: {{ $product->sku }}</p>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('products.edit', $product) }}" class="btn-secondary">Editar</a>
            <a href="{{ route('products.index') }}" class="btn-secondary">Volver</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            {{-- Info general --}}
            <div class="card">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        {{-- Avatar con inicial --}}
                        <div class="w-14 h-14 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-14 h-14 rounded-xl object-cover">
                            @else
                                <span class="text-2xl font-bold text-indigo-600">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">{{ $product->name }}</h2>
                            @if($product->description)
                                <p class="text-sm text-gray-500 mt-0.5">{{ $product->description }}</p>
                            @endif
                        </div>
                    </div>
                    @php
                        $statusClass = match($product->status->value ?? $product->status) {
                            'active'       => 'badge-green',
                            'inactive'     => 'badge-gray',
                            'discontinued' => 'badge-red',
                            default        => 'badge-gray',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $product->status->label() }}</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Tipo</p>
                        <p class="font-medium text-gray-900">{{ $product->type->label() }}</p>
                    </div>
                    @if($product->category)
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Categoría</p>
                            <p class="font-medium text-gray-900">{{ $product->category->name }}</p>
                        </div>
                    @endif
                    @if($product->unit)
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Unidad</p>
                            <p class="font-medium text-gray-900">{{ $product->unit }}</p>
                        </div>
                    @endif
                    @if($product->barcode)
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Código de barras</p>
                            <p class="font-medium text-gray-900 font-mono">{{ $product->barcode }}</p>
                        </div>
                    @endif
                </div>

                {{-- Badges de comportamiento --}}
                <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-2">
                    @if($product->is_purchasable)
                        <span class="badge badge-blue">Comprable</span>
                    @endif
                    @if($product->is_sellable)
                        <span class="badge badge-green">Vendible</span>
                    @endif
                    @if($product->is_producible)
                        <span class="badge badge-purple">Producible</span>
                    @endif
                    @if($product->track_batches)
                        <span class="badge badge-orange">Control lotes</span>
                    @endif
                    @if($product->track_expiry)
                        <span class="badge badge-yellow">Control vencimiento</span>
                    @endif
                </div>

                @if($product->notes)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Notas</p>
                        <p class="text-sm text-gray-700">{{ $product->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Stock por almacén --}}
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Stock por almacén</h3>
                    <a href="{{ route('inventory.index') }}?search={{ urlencode($product->name) }}" class="text-xs text-indigo-600 hover:underline">
                        Ver en inventario →
                    </a>
                </div>

                @if($product->inventory->count() > 0)
                    <div class="space-y-2">
                        @foreach($product->inventory as $inv)
                            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full {{ $inv->quantity > 0 ? 'bg-emerald-400' : 'bg-gray-300' }}"></div>
                                    <span class="text-sm font-medium text-gray-700">{{ $inv->warehouse->name ?? 'Almacén' }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-bold {{ $inv->quantity > 0 ? 'text-gray-900' : 'text-gray-400' }}">
                                        {{ number_format($inv->quantity, 3) }} {{ $product->unit }}
                                    </span>
                                    @if(isset($inv->reserved_quantity) && $inv->reserved_quantity > 0)
                                        <p class="text-xs text-amber-600">{{ number_format($inv->reserved_quantity, 3) }} reservado</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between text-sm">
                        <span class="font-semibold text-gray-700">Total stock</span>
                        <span class="font-bold {{ $product->total_stock > ($product->stock_minimum ?? 0) ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ number_format($product->total_stock, 3) }} {{ $product->unit }}
                        </span>
                    </div>
                @else
                    <p class="text-sm text-gray-400 py-4 text-center">Sin stock registrado</p>
                @endif
            </div>

            {{-- Recetas activas --}}
            @if($product->recipes->count() > 0)
                <div class="card">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Recetas de producción</h3>
                    <div class="space-y-2">
                        @foreach($product->recipes as $recipe)
                            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $recipe->name }}</p>
                                    <p class="text-xs text-gray-400">v{{ $recipe->version }} · {{ number_format($recipe->yield_quantity, 2) }} {{ $recipe->yield_unit }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($recipe->is_default)
                                        <span class="badge badge-purple">predeterminada</span>
                                    @endif
                                    <a href="{{ route('recipes.show', $recipe) }}" class="text-xs text-indigo-600 hover:underline">Ver →</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- Sidebar: precios y umbrales --}}
        <div class="space-y-6">

            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Costos y precios</h3>
                <div class="space-y-3 text-sm">
                    @if($product->cost)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Costo</span>
                            <span class="font-medium">S/ {{ number_format($product->cost, 4) }}</span>
                        </div>
                    @endif
                    @if($product->average_cost)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Costo prom.</span>
                            <span class="font-medium">S/ {{ number_format($product->average_cost, 4) }}</span>
                        </div>
                    @endif
                    @if($product->last_purchase_cost)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Último costo compra</span>
                            <span class="font-medium">S/ {{ number_format($product->last_purchase_cost, 4) }}</span>
                        </div>
                    @endif
                    @if($product->price)
                        <div class="flex justify-between pt-2 border-t border-gray-100">
                            <span class="font-semibold text-gray-900">Precio venta</span>
                            <span class="font-bold text-indigo-700">S/ {{ number_format($product->price, 2) }}</span>
                        </div>
                    @endif
                    @if($product->price && $product->cost)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Margen</span>
                            <span class="font-medium {{ $product->margin >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ number_format($product->margin, 1) }}%
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Umbrales de stock</h3>
                <div class="space-y-3 text-sm">
                    @if($product->stock_minimum)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Mínimo</span>
                            <span class="font-medium">{{ number_format($product->stock_minimum, 2) }} {{ $product->unit }}</span>
                        </div>
                    @endif
                    @if($product->stock_maximum)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Máximo</span>
                            <span class="font-medium">{{ number_format($product->stock_maximum, 2) }} {{ $product->unit }}</span>
                        </div>
                    @endif
                    @if($product->reorder_point)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Punto de reorden</span>
                            <span class="font-medium {{ $product->total_stock <= $product->reorder_point ? 'text-amber-600 font-bold' : '' }}">
                                {{ number_format($product->reorder_point, 2) }} {{ $product->unit }}
                            </span>
                        </div>
                    @endif
                    @if(!$product->stock_minimum && !$product->stock_maximum && !$product->reorder_point)
                        <p class="text-gray-400 text-xs">Sin umbrales configurados</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Acciones rápidas</h3>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('inventory.adjust') }}?productId={{ $product->id }}" class="btn-secondary text-sm text-center">
                        Ajustar stock
                    </a>
                    <a href="{{ route('inventory.kardex') }}?productId={{ $product->id }}" class="btn-secondary text-sm text-center">
                        Ver kardex
                    </a>
                    @if($product->is_producible)
                        <a href="{{ route('production.create') }}" class="btn-secondary text-sm text-center">
                            Nueva orden producción
                        </a>
                    @endif
                </div>
            </div>

            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Información</h3>
                <div class="space-y-2 text-xs text-gray-500">
                    <p>Creado: {{ $product->created_at->format('d/m/Y') }}</p>
                    @if($product->updated_at != $product->created_at)
                        <p>Actualizado: {{ $product->updated_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
