<div>
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('production.index') }}" class="hover:text-gray-700">Producción</a>
                <span>/</span>
                <span>{{ $orderId ? 'Editar Orden' : 'Nueva Orden' }}</span>
            </div>
            <h1 class="page-title">{{ $orderId ? 'Editar Orden de Producción' : 'Nueva Orden de Producción' }}</h1>
        </div>
        <a href="{{ route('production.index') }}" class="btn-secondary">Cancelar</a>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                <div class="card">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Datos de la orden</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div class="md:col-span-2">
                            <label class="form-label">Receta <span class="text-red-500">*</span></label>
                            <select wire:model.live="recipeId" class="form-input">
                                <option value="0">Seleccionar receta…</option>
                                @foreach($recipes as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }} — {{ $r->product->name ?? '' }}</option>
                                @endforeach
                            </select>
                            @error('recipeId') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        @if($selectedRecipe)
                            <div class="md:col-span-2 bg-indigo-50 border border-indigo-100 rounded-lg p-3 text-sm">
                                <div class="flex items-start gap-3">
                                    <div class="flex-1">
                                        <p class="font-medium text-indigo-900">{{ $selectedRecipe->name }}</p>
                                        <p class="text-indigo-600 text-xs mt-0.5">
                                            Rendimiento: {{ $selectedRecipe->yield_quantity }} {{ $selectedRecipe->yield_unit }} ·
                                            Costo unit.: S/ {{ number_format($selectedRecipe->unit_cost, 4) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="form-label">Cantidad planificada <span class="text-red-500">*</span></label>
                            <input wire:model.blur="plannedQuantity" type="number" step="0.001" min="0" class="form-input">
                            @error('plannedQuantity') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">Almacén de producción <span class="text-red-500">*</span></label>
                            <select wire:model="warehouseId" class="form-input">
                                <option value="0">Seleccionar almacén…</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                            @error('warehouseId') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">Inicio planificado <span class="text-red-500">*</span></label>
                            <input wire:model="plannedStartDate" type="date" class="form-input">
                            @error('plannedStartDate') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">Fin planificado</label>
                            <input wire:model="plannedEndDate" type="date" class="form-input">
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Responsable / Asignado a</label>
                            <select wire:model="assignedTo" class="form-input">
                                <option value="0">Sin asignar</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Notas / Instrucciones especiales</label>
                            <textarea wire:model="notes" rows="3" class="form-input" placeholder="Indicaciones adicionales para esta orden…"></textarea>
                        </div>

                    </div>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                @if($selectedRecipe)
                    <div class="card bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Costo estimado</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Materiales</span>
                                <span class="font-medium">S/ {{ number_format($estimatedMaterialCost, 2) }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-gray-200">
                                <span class="font-bold text-gray-900">Total estimado</span>
                                <span class="font-bold text-indigo-700">S/ {{ number_format($estimatedTotalCost, 2) }}</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-200 text-xs text-gray-400">
                            Basado en {{ $plannedQuantity ?: '0' }} unidades a producir
                        </div>
                    </div>
                @endif

                <div class="flex flex-col gap-2">
                    <button type="submit" class="btn-primary w-full">
                        <span wire:loading.remove>{{ $orderId ? 'Actualizar orden' : 'Crear orden' }}</span>
                        <span wire:loading>Guardando…</span>
                    </button>
                    <a href="{{ route('production.index') }}" class="btn-secondary w-full text-center">Cancelar</a>
                </div>

            </div>
        </div>
    </form>
</div>
