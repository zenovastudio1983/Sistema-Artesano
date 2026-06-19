<div>
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('recipes.index') }}" class="hover:text-gray-700">Recetas</a>
                <span>/</span>
                <span>{{ $recipe->name }}</span>
            </div>
            <h1 class="page-title">{{ $recipe->name }}</h1>
            @if($recipe->version)
                <p class="text-sm text-gray-500">Versión {{ $recipe->version }}</p>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('recipes.edit', $recipe) }}" class="btn-secondary">Editar</a>
            <a href="{{ route('recipes.index') }}" class="btn-secondary">Volver</a>
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
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $recipe->name }}</h2>
                        @if($recipe->description)
                            <p class="text-sm text-gray-500 mt-1">{{ $recipe->description }}</p>
                        @endif
                    </div>
                    <span class="badge {{ $recipe->is_active ? 'badge-green' : 'badge-gray' }}">
                        {{ $recipe->is_active ? 'Activa' : 'Inactiva' }}
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Producto final</p>
                        <p class="font-medium text-gray-900">{{ $recipe->product->name ?? '—' }}</p>
                        @if($recipe->product?->category)
                            <p class="text-xs text-gray-500">{{ $recipe->product->category->name }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Rendimiento</p>
                        <p class="font-medium text-gray-900">{{ number_format($recipe->yield_quantity, 2) }} {{ $recipe->yield_unit }}</p>
                    </div>
                    @if($recipe->production_time_minutes)
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Tiempo producción</p>
                            <p class="font-medium text-gray-900">{{ $recipe->production_time_minutes }} min</p>
                        </div>
                    @endif
                    @if($recipe->is_default)
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Clasificación</p>
                            <span class="badge badge-purple">Receta predeterminada</span>
                        </div>
                    @endif
                </div>

                @if($recipe->instructions)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Instrucciones</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $recipe->instructions }}</p>
                    </div>
                @endif
            </div>

            {{-- Ingredientes --}}
            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">
                    Ingredientes
                    <span class="text-gray-400 font-normal">({{ $recipe->ingredients->count() }})</span>
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-2 pr-4 text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="text-left py-2 pr-4 text-xs font-medium text-gray-500 uppercase">Ingrediente</th>
                                <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Merma %</th>
                                <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Costo unit.</th>
                                <th class="text-right py-2 pl-4 text-xs font-medium text-gray-500 uppercase">Costo total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($recipe->ingredients as $i => $ingredient)
                                <tr>
                                    <td class="py-3 pr-4 text-gray-400">{{ $i + 1 }}</td>
                                    <td class="py-3 pr-4">
                                        <p class="font-medium text-gray-900">{{ $ingredient->product->name ?? '—' }}</p>
                                        @if($ingredient->is_optional)
                                            <span class="text-xs text-amber-600">opcional</span>
                                        @endif
                                        @if($ingredient->notes)
                                            <p class="text-xs text-gray-400">{{ $ingredient->notes }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right text-gray-700">
                                        {{ number_format($ingredient->quantity, 3) }} {{ $ingredient->unit }}
                                    </td>
                                    <td class="py-3 px-4 text-right {{ $ingredient->scrap_percentage > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                        {{ $ingredient->scrap_percentage > 0 ? $ingredient->scrap_percentage . '%' : '—' }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-gray-700">
                                        S/ {{ number_format($ingredient->unit_cost, 4) }}
                                    </td>
                                    <td class="py-3 pl-4 text-right font-medium text-gray-900">
                                        S/ {{ number_format($ingredient->total_cost ?? ($ingredient->quantity * (1 + $ingredient->scrap_percentage / 100) * $ingredient->unit_cost), 4) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Sidebar: estructura de costos --}}
        <div class="space-y-6">

            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Estructura de costos</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Materiales</span>
                        <span class="font-medium">S/ {{ number_format($recipe->material_cost, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Mano de obra</span>
                        <span class="font-medium">S/ {{ number_format($recipe->labor_cost, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Gastos generales</span>
                        <span class="font-medium">S/ {{ number_format($recipe->overhead_cost, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-100">
                        <span class="font-semibold text-gray-900">Costo total lote</span>
                        <span class="font-bold text-gray-900">S/ {{ number_format($recipe->total_cost, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-100 text-indigo-700">
                        <span class="font-semibold">Costo unitario</span>
                        <span class="font-bold text-lg">S/ {{ number_format($recipe->unit_cost, 4) }}</span>
                    </div>
                </div>

                {{-- Barra de distribución --}}
                @if($recipe->total_cost > 0)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-400 mb-2">Distribución de costos</p>
                        <div class="flex rounded-full overflow-hidden h-2">
                            <div class="bg-indigo-500 h-2" style="width: {{ round($recipe->material_cost / $recipe->total_cost * 100) }}%"></div>
                            <div class="bg-amber-400 h-2" style="width: {{ round($recipe->labor_cost / $recipe->total_cost * 100) }}%"></div>
                            <div class="bg-gray-300 h-2 flex-1"></div>
                        </div>
                        <div class="flex gap-4 mt-2 text-xs text-gray-500">
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-indigo-500 inline-block"></span>Materiales</span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>Mano obra</span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>G. generales</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Información</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-400">Versión</p>
                        <p class="font-medium text-gray-900">{{ $recipe->version ?? '1.0' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Ingredientes</p>
                        <p class="font-medium text-gray-900">{{ $recipe->ingredients->count() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Creada</p>
                        <p class="font-medium text-gray-900">{{ $recipe->created_at->format('d/m/Y') }}</p>
                    </div>
                    @if($recipe->updated_at != $recipe->created_at)
                        <div>
                            <p class="text-xs text-gray-400">Actualizada</p>
                            <p class="font-medium text-gray-900">{{ $recipe->updated_at->format('d/m/Y') }}</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
