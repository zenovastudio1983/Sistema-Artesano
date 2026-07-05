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

                {{-- Imagen principal --}}
                <div class="card" x-data="{ dragging: false }">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Imagen del producto</h2>

                    @php $currentImage = $productId ? \App\Domains\Products\Models\Product::find($productId)?->image_url : null; @endphp

                    <div class="flex gap-6 items-start flex-wrap">

                        {{-- Preview --}}
                        <div class="w-28 h-28 rounded-xl border-2 border-dashed border-gray-200 overflow-hidden flex-shrink-0 bg-gray-50 flex items-center justify-center relative">
                            @if($mainImage)
                                <img src="{{ $mainImage->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview">
                            @elseif($currentImage && !$deleteImage)
                                <img src="{{ $currentImage }}" class="w-full h-full object-cover" alt="Imagen actual">
                            @else
                                <svg class="w-10 h-10 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @endif
                        </div>

                        {{-- Controles --}}
                        <div class="flex-1 min-w-0">
                            <label class="flex flex-col items-start gap-2 cursor-pointer group">
                                <div class="flex items-center gap-2 bg-white border border-gray-200 hover:border-indigo-400 hover:bg-indigo-50 text-gray-600 hover:text-indigo-700 rounded-lg px-4 py-2 text-sm font-medium transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    Elegir imagen
                                </div>
                                <input wire:model="mainImage" type="file" accept="image/*" class="sr-only">
                            </label>
                            <p class="text-xs text-gray-400 mt-2">JPG, PNG o WebP · máx. 2 MB · La imagen es opcional</p>

                            @error('mainImage') <p class="form-error mt-1">{{ $message }}</p> @enderror

                            @if($mainImage)
                                <div class="flex items-center gap-2 mt-2 text-xs text-indigo-600">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Nueva imagen seleccionada
                                </div>
                            @endif

                            @if($currentImage && !$mainImage)
                                <label class="flex items-center gap-2 mt-3 cursor-pointer group">
                                    <input wire:model="deleteImage" type="checkbox" class="w-3.5 h-3.5 rounded text-red-500">
                                    <span class="text-xs text-gray-400 group-hover:text-red-500 transition-colors">Eliminar imagen actual</span>
                                </label>
                            @endif
                        </div>

                    </div>

                    <div wire:loading wire:target="mainImage" class="mt-3 flex items-center gap-2 text-xs text-indigo-500">
                        <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Subiendo imagen…
                    </div>
                </div>

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
                            <label class="form-label">Costo estándar ({{ config('erp.currency_symbol') }})</label>
                            <input wire:model.blur="cost" type="number" step="0.0001" min="0" class="form-input">
                            @error('cost') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">Precio de venta ({{ config('erp.currency_symbol') }})</label>
                            <input wire:model.blur="price" type="number" step="0.0001" min="0" class="form-input">
                            @error('price') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">Margen (%)</label>
                            <input wire:model="marginPercent" type="number" step="0.01" class="form-input bg-gray-50" readonly>
                        </div>

                        <div>
                            <label class="form-label">Precio mínimo ({{ config('erp.currency_symbol') }})</label>
                            <input wire:model="minPrice" type="number" step="0.0001" min="0" class="form-input">
                        </div>

                        <div>
                            <label class="form-label">Costo estándar real ({{ config('erp.currency_symbol') }})</label>
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


                    </div>
                </div>

                {{-- Fechas de elaboración y vencimiento --}}
                @if($is_producible || $track_batches || $track_expiry)
                <div class="card">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Fechas del producto</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        @if($is_producible || $track_batches)
                        <div>
                            <label class="form-label">Fecha de elaboración</label>
                            <input wire:model.live="manufacturingDate" type="date" class="form-input">
                            @error('manufacturingDate') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        @endif

                        @if($track_expiry)
                        <div>
                            <label class="form-label">Fecha de vencimiento</label>
                            <input wire:model.live="expiryDate" type="date" class="form-input">
                            @error('expiryDate') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        @endif

                        @if($shelfLifeDays !== '')
                        <div class="md:col-span-2">
                            <div class="flex items-center gap-2 bg-indigo-50 border border-indigo-100 rounded-lg px-4 py-3 text-sm">
                                <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-indigo-700">Vida útil calculada: <strong>{{ $shelfLifeDays }} días</strong></span>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
                @endif

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
                            <input wire:model.live="is_producible" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Producible</p>
                                <p class="text-xs text-gray-400">Tiene receta de fabricación</p>
                            </div>
                        </label>
                        <hr class="border-gray-100">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input wire:model.live="track_batches" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
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

                {{-- Publicación en tienda --}}
                <div class="card">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Tienda pública</h3>

                    <label class="flex items-center gap-3 cursor-pointer mb-4">
                        <input wire:model.live="is_published" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Publicar en tienda</p>
                            <p class="text-xs text-gray-400">Visible en el catálogo público</p>
                        </div>
                    </label>

                    @if($is_published)
                    <div class="space-y-3">
                        <div>
                            <label class="form-label">Slug (URL)</label>
                            <div class="flex rounded-lg overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-indigo-400">
                                <span class="px-2 py-2 bg-gray-50 text-xs text-gray-400 border-r border-gray-200 flex items-center">/tienda/</span>
                                <input wire:model="publicSlug" type="text"
                                       class="flex-1 px-2 py-2 text-sm focus:outline-none bg-white"
                                       placeholder="mi-producto">
                            </div>
                            @error('publicSlug') <p class="form-error">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-400 mt-1">Solo letras, números y guiones</p>
                        </div>

                        <div>
                            <label class="form-label">Descripción para clientes</label>
                            <textarea wire:model="publicDescription" rows="3"
                                      class="form-input text-sm"
                                      placeholder="Descripción que verán los clientes en la tienda…"></textarea>
                            <p class="text-xs text-gray-400 mt-1">Si no se completa, se usa la descripción interna</p>
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <label class="flex items-center gap-3 cursor-pointer mb-3">
                                <input wire:model.live="is_made_to_order" type="checkbox" class="w-4 h-4 rounded text-amber-500">
                                <div>
                                    <p class="text-sm font-medium text-gray-700">A pedido</p>
                                    <p class="text-xs text-gray-400">Se fabrica bajo demanda</p>
                                </div>
                            </label>

                            @if($is_made_to_order)
                            <div>
                                <label class="form-label">Días de entrega estimados</label>
                                <input wire:model="leadTimeDays" type="number" min="1" max="365"
                                       class="form-input" placeholder="Ej: 7">
                                @error('leadTimeDays') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
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
