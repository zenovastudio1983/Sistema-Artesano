<div>
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('products.index') }}" class="hover:text-gray-700">Productos</a>
                <span>/</span>
                <span>{{ $productId ? 'Editar' : 'Nuevo' }}</span>
            </div>
            <h1 class="page-title">{{ $productId ? 'Editar Producto' : 'Nuevo Producto' }}</h1>
        </div>
        <a href="{{ route('products.index') }}" class="btn-secondary">Cancelar</a>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Columna principal --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Identificación --}}
                <div class="card">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Identificación</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div class="md:col-span-2">
                            <label class="form-label">Nombre <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" class="form-input" placeholder="Nombre del producto">
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">SKU / Código interno</label>
                            <input wire:model="sku" type="text" class="form-input" placeholder="Ej: MP-001">
                            @error('sku') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">Código de barras</label>
                            <input wire:model="barcode" type="text" class="form-input" placeholder="EAN-13, UPC…">
                        </div>

                        <div>
                            <label class="form-label">Tipo <span class="text-red-500">*</span></label>
                            <select wire:model.live="type" class="form-input">
                                @foreach($types as $t)
                                    <option value="{{ $t->value }}">{{ $t->label() }}</option>
                                @endforeach
                            </select>
                            @error('type') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">Categoría</label>
                            <select wire:model="categoryId" class="form-input">
                                <option value="0">Sin categoría</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Unidad principal</label>
                            <input wire:model="unit" type="text" class="form-input" placeholder="Ej: kg, litro, unidad">
                        </div>

                        <div>
                            <label class="form-label">Estado</label>
                            <select wire:model="status" class="form-input">
                                @foreach($statuses as $s)
                                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Descripción</label>
                            <textarea wire:model="description" rows="2" class="form-input" placeholder="Descripción del producto…"></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Notas internas</label>
                            <textarea wire:model="notes" rows="2" class="form-input" placeholder="Notas de uso interno…"></textarea>
                        </div>

                    </div>
                </div>

                {{-- Costos y precios --}}
                <div class="card">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Costos y precios</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <div>
                            <label class="form-label">Costo estándar (S/)</label>
                            <input wire:model.blur="cost" type="number" step="0.0001" min="0" class="form-input">
                            @error('cost') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">Precio de venta (S/)</label>
                            <input wire:model.blur="price" type="number" step="0.0001" min="0" class="form-input">
                            @error('price') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">Margen (%)</label>
                            <input wire:model="marginPercent" type="number" step="0.01" class="form-input bg-gray-50" readonly>
                        </div>

                        <div>
                            <label class="form-label">Precio mínimo (S/)</label>
                            <input wire:model="minPrice" type="number" step="0.0001" min="0" class="form-input">
                        </div>

                        <div>
                            <label class="form-label">Costo estándar real (S/)</label>
                            <input wire:model="standardCost" type="number" step="0.0001" min="0" class="form-input">
                        </div>

                    </div>
                </div>

                {{-- Control de stock --}}
                <div class="card">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Control de stock</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <div>
                            <label class="form-label">Stock mínimo</label>
                            <input wire:model="stockMinimum" type="number" step="0.001" min="0" class="form-input">
                        </div>

                        <div>
                            <label class="form-label">Stock máximo</label>
                            <input wire:model="stockMaximum" type="number" step="0.001" min="0" class="form-input">
                        </div>

                        <div>
                            <label class="form-label">Punto de reorden</label>
                            <input wire:model="reorderPoint" type="number" step="0.001" min="0" class="form-input">
                        </div>

                    </div>
                </div>

                {{-- Datos físicos --}}
                <div class="card">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Datos físicos (opcional)</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                        <div>
                            <label class="form-label">Peso</label>
                            <input wire:model="weight" type="number" step="0.001" min="0" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Unidad peso</label>
                            <select wire:model="weightUnit" class="form-input">
                                <option value="kg">kg</option>
                                <option value="g">g</option>
                                <option value="lb">lb</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Volumen</label>
                            <input wire:model="volume" type="number" step="0.001" min="0" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Unidad volumen</label>
                            <select wire:model="volumeUnit" class="form-input">
                                <option value="L">L</option>
                                <option value="ml">ml</option>
                                <option value="m3">m³</option>
                            </select>
                        </div>

                        @if($track_expiry)
                            <div class="md:col-span-4">
                                <label class="form-label">Vida útil (días)</label>
                                <input wire:model="shelfLifeDays" type="number" min="1" class="form-input w-40">
                            </div>
                        @endif

                    </div>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                <div class="card">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Comportamiento</h3>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input wire:model="is_purchasable" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Comprable</p>
                                <p class="text-xs text-gray-400">Aparece en órdenes de compra</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input wire:model="is_sellable" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Vendible</p>
                                <p class="text-xs text-gray-400">Aparece en ventas</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input wire:model="is_producible" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Producible</p>
                                <p class="text-xs text-gray-400">Tiene receta de fabricación</p>
                            </div>
                        </label>
                        <hr class="border-gray-100">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input wire:model="track_batches" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Control por lotes</p>
                                <p class="text-xs text-gray-400">Trazabilidad de número de lote</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input wire:model.live="track_expiry" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Control de vencimiento</p>
                                <p class="text-xs text-gray-400">Registra fecha de caducidad</p>
                            </div>
                        </label>
                    </div>
                </div>

                @if($type && in_array($type, ['finished_product', 'semi_finished']))
                    <div class="card bg-indigo-50 border border-indigo-100">
                        <p class="text-xs font-medium text-indigo-700 mb-1">Tipo seleccionado</p>
                        <p class="text-sm font-semibold text-indigo-900">
                            {{ collect($types)->firstWhere(fn($t) => $t->value === $type)?->label() ?? $type }}
                        </p>
                        <p class="text-xs text-indigo-600 mt-1">Este tipo puede tener receta de producción</p>
                    </div>
                @endif

                <div class="flex flex-col gap-2">
                    <button type="submit" class="btn-primary w-full">
                        <span wire:loading.remove>{{ $productId ? 'Actualizar producto' : 'Crear producto' }}</span>
                        <span wire:loading>Guardando…</span>
                    </button>
                    <a href="{{ route('products.index') }}" class="btn-secondary w-full text-center">Cancelar</a>
                </div>

            </div>
        </div>
    </form>
</div>
