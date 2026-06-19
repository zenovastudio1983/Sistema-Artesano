<div>
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('recipes.index') }}" class="hover:text-gray-700">Recetas</a>
                <span>/</span>
                <span>{{ $recipeId ? 'Editar Receta' : 'Nueva Receta' }}</span>
            </div>
            <h1 class="page-title">{{ $recipeId ? 'Editar Receta' : 'Nueva Receta' }}</h1>
        </div>
        <a href="{{ route('recipes.index') }}" class="btn-secondary">Cancelar</a>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                {{-- Info general --}}
                <div class="card">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Información general</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div class="md:col-span-2">
                            <label class="form-label">Producto final <span class="text-red-500">*</span></label>
                            <select wire:model="productId" class="form-input">
                                <option value="0">Seleccionar producto terminado…</option>
                                @foreach($finishedProducts as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            @error('productId') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Nombre de la receta <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" class="form-input" placeholder="Ej: Receta estándar salsa roja">
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">Versión</label>
                            <input wire:model="version" type="text" class="form-input" placeholder="1.0">
                        </div>

                        <div>
                            <label class="form-label">Tiempo de producción (min)</label>
                            <input wire:model="productionTimeMinutes" type="number" min="0" class="form-input">
                        </div>

                        <div>
                            <label class="form-label">Rendimiento (cantidad) <span class="text-red-500">*</span></label>
                            <input wire:model.blur="yieldQuantity" type="number" step="0.001" min="0" class="form-input">
                            @error('yieldQuantity') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">Unidad de rendimiento</label>
                            <input wire:model="yieldUnit" type="text" class="form-input" placeholder="Ej: kg, litros, porciones">
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Descripción</label>
                            <textarea wire:model="description" rows="2" class="form-input" placeholder="Descripción breve…"></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Instrucciones de elaboración</label>
                            <textarea wire:model="instructions" rows="4" class="form-input" placeholder="Pasos del proceso…"></textarea>
                        </div>

                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input wire:model="is_active" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
                                <span class="text-sm text-gray-700">Receta activa</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input wire:model="is_default" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
                                <span class="text-sm text-gray-700">Predeterminada</span>
                            </label>
                        </div>

                    </div>
                </div>

                {{-- Ingredientes --}}
                <div class="card">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-gray-700">Ingredientes / Materias primas</h2>
                        <button type="button" wire:click="addIngredient" class="btn-secondary text-xs">
                            + Agregar ingrediente
                        </button>
                    </div>

                    @error('ingredients') <p class="form-error mb-3">{{ $message }}</p> @enderror

                    <div class="space-y-3">
                        @foreach($ingredients as $index => $ing)
                            <div class="border border-gray-100 rounded-lg p-3 bg-gray-50">
                                <div class="grid grid-cols-12 gap-2 items-end">

                                    <div class="col-span-12 md:col-span-4">
                                        @if($index === 0)
                                            <label class="form-label text-xs">Ingrediente</label>
                                        @endif
                                        <select wire:model="ingredients.{{ $index }}.product_id" class="form-input text-sm">
                                            <option value="0">Seleccionar…</option>
                                            @foreach($allProducts as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                        @error("ingredients.{$index}.product_id") <p class="form-error text-xs">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="col-span-3 md:col-span-2">
                                        @if($index === 0)
                                            <label class="form-label text-xs">Cantidad</label>
                                        @endif
                                        <input wire:model.blur="ingredients.{{ $index }}.quantity"
                                               type="number" step="0.001" min="0"
                                               class="form-input text-sm text-right">
                                        @error("ingredients.{$index}.quantity") <p class="form-error text-xs">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="col-span-2 md:col-span-1">
                                        @if($index === 0)
                                            <label class="form-label text-xs">Unidad</label>
                                        @endif
                                        <input wire:model="ingredients.{{ $index }}.unit" type="text"
                                               class="form-input text-sm" placeholder="kg">
                                    </div>

                                    <div class="col-span-3 md:col-span-2">
                                        @if($index === 0)
                                            <label class="form-label text-xs">Merma %</label>
                                        @endif
                                        <input wire:model.blur="ingredients.{{ $index }}.scrap_percentage"
                                               type="number" step="0.01" min="0" max="100"
                                               class="form-input text-sm text-right">
                                    </div>

                                    <div class="col-span-3 md:col-span-2">
                                        @if($index === 0)
                                            <label class="form-label text-xs">Costo unit.</label>
                                        @endif
                                        <input wire:model.blur="ingredients.{{ $index }}.unit_cost"
                                               type="number" step="0.0001" min="0"
                                               class="form-input text-sm text-right">
                                    </div>

                                    <div class="col-span-1 flex items-end justify-end">
                                        <button type="button" wire:click="removeIngredient({{ $index }})"
                                                class="text-red-400 hover:text-red-600 p-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="col-span-12 flex items-center justify-between text-xs text-gray-500">
                                        <label class="flex items-center gap-1 cursor-pointer">
                                            <input wire:model="ingredients.{{ $index }}.is_optional" type="checkbox" class="w-3 h-3 rounded">
                                            opcional
                                        </label>
                                        <span>Costo línea: <span class="font-semibold text-gray-700">S/ {{ number_format($ing['total_cost'] ?? 0, 4) }}</span></span>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" wire:click="addIngredient"
                            class="mt-3 w-full py-2 border-2 border-dashed border-gray-200 rounded-lg text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 transition-colors">
                        + Agregar ingrediente
                    </button>
                </div>

            </div>

            {{-- Sidebar: costos --}}
            <div class="space-y-6">

                <div class="card">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Costos adicionales</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="form-label">Mano de obra (S/)</label>
                            <input wire:model.blur="laborCost" type="number" step="0.01" min="0" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Gastos generales (S/)</label>
                            <input wire:model.blur="overheadCost" type="number" step="0.01" min="0" class="form-input">
                        </div>
                    </div>
                </div>

                <div class="card bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Resumen de costos</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Materiales</span>
                            <span class="font-medium">S/ {{ number_format($materialCost, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Mano de obra</span>
                            <span class="font-medium">S/ {{ number_format($laborCost, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">G. generales</span>
                            <span class="font-medium">S/ {{ number_format($overheadCost, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200">
                            <span class="font-bold text-gray-900">Total lote</span>
                            <span class="font-bold text-gray-900">S/ {{ number_format($totalCost, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200 text-indigo-700">
                            <span class="font-bold">Costo unitario</span>
                            <span class="font-bold text-lg">S/ {{ number_format($unitCost, 4) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <button type="submit" class="btn-primary w-full">
                        <span wire:loading.remove>{{ $recipeId ? 'Actualizar receta' : 'Crear receta' }}</span>
                        <span wire:loading>Guardando…</span>
                    </button>
                    <a href="{{ route('recipes.index') }}" class="btn-secondary w-full text-center">Cancelar</a>
                </div>

            </div>
        </div>
    </form>
</div>
