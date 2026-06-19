<div>
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('sales.index') }}" class="hover:text-gray-700">Ventas</a>
                <span>/</span>
                <span>{{ $saleId ? 'Editar Venta' : 'Nueva Venta' }}</span>
            </div>
            <h1 class="page-title">{{ $saleId ? 'Editar Venta' : 'Nueva Venta' }}</h1>
        </div>
        <a href="{{ route('sales.index') }}" class="btn-secondary">Cancelar</a>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                {{-- Datos generales --}}
                <div class="card">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Datos generales</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div class="md:col-span-2">
                            <label class="form-label">Cliente</label>
                            <select wire:model="customerId" class="form-input">
                                <option value="0">Cliente general (sin cuenta)</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->display_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Almacén <span class="text-red-500">*</span></label>
                            <select wire:model="warehouseId" class="form-input">
                                <option value="0">Seleccionar…</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Fecha de venta <span class="text-red-500">*</span></label>
                            <input wire:model="saleDate" type="date" class="form-input">
                            @error('saleDate') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">Fecha de vencimiento</label>
                            <input wire:model="dueDate" type="date" class="form-input">
                        </div>

                        <div>
                            <label class="form-label">Referencia / Nro. documento</label>
                            <input wire:model="reference" type="text" class="form-input" placeholder="Ej: F001-00001">
                        </div>

                        <div>
                            <label class="form-label">Moneda</label>
                            <select wire:model="currency" class="form-input">
                                <option value="PEN">PEN — Sol peruano</option>
                                <option value="USD">USD — Dólar</option>
                                <option value="EUR">EUR — Euro</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Notas</label>
                            <textarea wire:model="notes" rows="2" class="form-input" placeholder="Observaciones…"></textarea>
                        </div>

                    </div>
                </div>

                {{-- Ítems --}}
                <div class="card">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-gray-700">Productos vendidos</h2>
                        <button type="button" wire:click="addItem" class="btn-secondary text-xs">
                            + Agregar línea
                        </button>
                    </div>

                    @error('items') <p class="form-error mb-3">{{ $message }}</p> @enderror

                    <div class="space-y-3">
                        @foreach($items as $index => $item)
                            <div class="border border-gray-100 rounded-lg p-3 bg-gray-50">
                                <div class="grid grid-cols-12 gap-2 items-end">

                                    <div class="col-span-12 md:col-span-5">
                                        @if($index === 0)
                                            <label class="form-label text-xs">Producto</label>
                                        @endif
                                        <select wire:model="items.{{ $index }}.product_id" class="form-input text-sm">
                                            <option value="0">Seleccionar…</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                        @error("items.{$index}.product_id") <p class="form-error text-xs">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="col-span-4 md:col-span-2">
                                        @if($index === 0)
                                            <label class="form-label text-xs">Cantidad</label>
                                        @endif
                                        <input wire:model.blur="items.{{ $index }}.quantity"
                                               type="number" step="0.001" min="0"
                                               class="form-input text-sm text-right">
                                        @error("items.{$index}.quantity") <p class="form-error text-xs">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="col-span-4 md:col-span-2">
                                        @if($index === 0)
                                            <label class="form-label text-xs">Precio unit.</label>
                                        @endif
                                        <input wire:model.blur="items.{{ $index }}.unit_price"
                                               type="number" step="0.0001" min="0"
                                               class="form-input text-sm text-right">
                                        @error("items.{$index}.unit_price") <p class="form-error text-xs">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="col-span-3 md:col-span-2">
                                        @if($index === 0)
                                            <label class="form-label text-xs">Dto. %</label>
                                        @endif
                                        <input wire:model.blur="items.{{ $index }}.discount_percent"
                                               type="number" step="0.01" min="0" max="100"
                                               class="form-input text-sm text-right">
                                    </div>

                                    <div class="col-span-1 flex items-end justify-end">
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                                class="text-red-400 hover:text-red-600 p-1 mt-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="col-span-12 text-right text-xs text-gray-500">
                                        Subtotal: <span class="font-semibold text-gray-800">{{ number_format($item['subtotal'] ?? 0, 2) }}</span>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" wire:click="addItem"
                            class="mt-3 w-full py-2 border-2 border-dashed border-gray-200 rounded-lg text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 transition-colors">
                        + Agregar producto
                    </button>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                <div class="card">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Condiciones comerciales</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="form-label">Descuento global (%)</label>
                            <input wire:model.blur="discountPercent" type="number" step="0.01" min="0" max="100" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">IGV (%)</label>
                            <input wire:model.blur="taxRate" type="number" step="0.01" min="0" class="form-input">
                        </div>
                    </div>
                </div>

                <div class="card bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Resumen</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="font-medium">{{ number_format($subtotal, 2) }}</span>
                        </div>
                        @if((float)$discountPercent > 0)
                            <div class="flex justify-between text-amber-600">
                                <span>Descuento ({{ $discountPercent }}%)</span>
                                <span class="font-medium">-{{ number_format($discountAmount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-500">IGV ({{ $taxRate }}%)</span>
                            <span class="font-medium">{{ number_format($taxAmount, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200">
                            <span class="font-bold text-gray-900">TOTAL</span>
                            <span class="font-bold text-lg text-indigo-700">{{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <button type="submit" class="btn-primary w-full">
                        <span wire:loading.remove>{{ $saleId ? 'Actualizar venta' : 'Crear venta' }}</span>
                        <span wire:loading>Guardando…</span>
                    </button>
                    <a href="{{ route('sales.index') }}" class="btn-secondary w-full text-center">Cancelar</a>
                </div>

            </div>
        </div>
    </form>
</div>
