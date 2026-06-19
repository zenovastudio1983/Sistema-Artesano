<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <a href="{{ route('suppliers.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Proveedores</a>
                <span class="text-gray-300">/</span>
                <span class="text-sm text-gray-700">{{ $supplierId ? 'Editar' : 'Nuevo' }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $supplierId ? 'Editar proveedor' : 'Nuevo proveedor' }}</h1>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">

        {{-- Datos fiscales --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Datos fiscales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Razón social *</label>
                    <input wire:model="business_name" type="text" class="form-input @error('business_name') border-red-500 @enderror">
                    @error('business_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Nombre comercial</label>
                    <input wire:model="trade_name" type="text" class="form-input">
                </div>
                <div>
                    <label class="form-label">RUC / NIF</label>
                    <input wire:model="tax_id" type="text" class="form-input font-mono">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input wire:model="email" type="email" class="form-input">
                </div>
                <div>
                    <label class="form-label">Teléfono</label>
                    <input wire:model="phone" type="text" class="form-input">
                </div>
                <div>
                    <label class="form-label">Ciudad</label>
                    <input wire:model="city" type="text" class="form-input">
                </div>
                <div>
                    <label class="form-label">Dirección</label>
                    <input wire:model="address" type="text" class="form-input">
                </div>
            </div>
        </div>

        {{-- Contacto --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Persona de contacto</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Nombre</label>
                    <input wire:model="contact_name" type="text" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email de contacto</label>
                    <input wire:model="contact_email" type="email" class="form-input">
                </div>
                <div>
                    <label class="form-label">Teléfono de contacto</label>
                    <input wire:model="contact_phone" type="text" class="form-input">
                </div>
            </div>
        </div>

        {{-- Condiciones comerciales --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Condiciones comerciales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Días de crédito</label>
                    <input wire:model="payment_days" type="number" min="0" class="form-input">
                </div>
                <div>
                    <label class="form-label">Límite de crédito</label>
                    <input wire:model="credit_limit" type="number" step="0.01" min="0" class="form-input">
                </div>
                <div>
                    <label class="form-label">Moneda</label>
                    <select wire:model="currency" class="form-input">
                        <option value="PEN">PEN - Sol</option>
                        <option value="USD">USD - Dólar</option>
                        <option value="EUR">EUR - Euro</option>
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <label class="form-label">Notas internas</label>
                    <textarea wire:model="notes" rows="3" class="form-input"></textarea>
                </div>
                <div class="flex items-center space-x-2">
                    <input wire:model="is_active" type="checkbox" id="is_active" class="rounded border-gray-300 text-indigo-600">
                    <label for="is_active" class="text-sm text-gray-700">Proveedor activo</label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('suppliers.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                <span wire:loading.remove>{{ $supplierId ? 'Actualizar' : 'Crear proveedor' }}</span>
                <span wire:loading>Guardando...</span>
            </button>
        </div>

    </form>

</div>
