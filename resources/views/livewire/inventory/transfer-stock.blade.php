<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <a href="{{ route('inventory.movements') }}" class="text-sm text-gray-400 hover:text-gray-600">Movimientos</a>
                <span class="text-gray-300">/</span>
                <span class="text-sm text-gray-700">Transferencia</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Transferencia de Stock</h1>
            <p class="text-sm text-gray-500 mt-1">Mueve inventario de un almacén a otro</p>
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Almacén origen *</label>
                    <select wire:model="sourceWarehouseId" class="form-input @error('sourceWarehouseId') border-red-500 @enderror">
                        <option value="0">Seleccionar origen...</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                    @error('sourceWarehouseId')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Almacén destino *</label>
                    <select wire:model="destWarehouseId" class="form-input @error('destWarehouseId') border-red-500 @enderror">
                        <option value="0">Seleccionar destino...</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                    @error('destWarehouseId')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            @if($sourceWarehouseId && $destWarehouseId && $sourceWarehouseId === $destWarehouseId)
            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-700">
                El almacén origen y destino deben ser diferentes.
            </div>
            @endif

            <div>
                <label class="form-label">Cantidad *</label>
                <input wire:model="quantity" type="number" step="0.0001" min="0.0001"
                       class="form-input @error('quantity') border-red-500 @enderror"
                       placeholder="0.00">
                @error('quantity')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Referencia</label>
                <input wire:model="reference" type="text" class="form-input" placeholder="Autogenerada si se deja vacío">
            </div>

            <div>
                <label class="form-label">Notas</label>
                <textarea wire:model="notes" rows="3" class="form-input" placeholder="Motivo de la transferencia..."></textarea>
            </div>

        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('inventory.movements') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                <span wire:loading.remove>Registrar transferencia</span>
                <span wire:loading>Guardando...</span>
            </button>
        </div>

    </form>

</div>
