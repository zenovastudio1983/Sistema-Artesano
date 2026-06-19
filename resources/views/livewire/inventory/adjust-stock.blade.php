<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <a href="{{ route('inventory.movements') }}" class="text-sm text-gray-400 hover:text-gray-600">Movimientos</a>
                <span class="text-gray-300">/</span>
                <span class="text-sm text-gray-700">Ajuste</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Ajuste de Stock</h1>
            <p class="text-sm text-gray-500 mt-1">Registra una entrada o salida manual de inventario</p>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">

            <div>
                <label class="form-label">Producto *</label>
                <select wire:model="productId" class="form-input @error('productId') border-red-500 @enderror">
                    <option value="0">Seleccionar producto...</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                    @endforeach
                </select>
                @error('productId')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Almacén *</label>
                <select wire:model="warehouseId" class="form-input @error('warehouseId') border-red-500 @enderror">
                    <option value="0">Seleccionar almacén...</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
                @error('warehouseId')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Tipo de ajuste *</label>
                <div class="flex space-x-4 mt-1">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" wire:model="adjustmentType" value="positive"
                               class="text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm text-gray-700">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700">+ Entrada (positivo)</span>
                        </span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" wire:model="adjustmentType" value="negative"
                               class="text-red-500 focus:ring-red-400">
                        <span class="text-sm text-gray-700">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">− Salida (negativo)</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Cantidad *</label>
                    <input wire:model="quantity" type="number" step="0.0001" min="0.0001"
                           class="form-input @error('quantity') border-red-500 @enderror"
                           placeholder="0.00">
                    @error('quantity')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Costo unitario</label>
                    <input wire:model="unitCost" type="number" step="0.0001" min="0"
                           class="form-input" placeholder="0.0000">
                </div>
            </div>

            <div>
                <label class="form-label">Referencia</label>
                <input wire:model="reference" type="text" class="form-input" placeholder="Ej: AJU-001">
            </div>

            <div>
                <label class="form-label">Motivo / notas</label>
                <textarea wire:model="notes" rows="3" class="form-input"
                          placeholder="Motivo del ajuste..."></textarea>
            </div>

        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('inventory.movements') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                <span wire:loading.remove>Registrar ajuste</span>
                <span wire:loading>Guardando...</span>
            </button>
        </div>

    </form>

</div>
