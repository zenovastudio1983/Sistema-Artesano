<div>
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('production.index') }}" class="hover:text-gray-700">Producción</a>
                <span>/</span>
                <span>{{ $order->order_number }}</span>
            </div>
            <h1 class="page-title">Orden {{ $order->order_number }}</h1>
        </div>
        <div class="flex gap-2">
            @if($order->status->isEditable())
                <a href="{{ route('production.edit', $order) }}" class="btn-secondary">Editar</a>
            @endif
            <a href="{{ route('production.index') }}" class="btn-secondary">Volver</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            {{-- Cabecera --}}
            <div class="card">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $order->order_number }}</h2>
                        <p class="text-sm text-gray-500">
                            {{ $order->planned_start_date?->format('d/m/Y') }}
                            @if($order->planned_end_date)
                                → {{ $order->planned_end_date->format('d/m/Y') }}
                            @endif
                        </p>
                    </div>
                    @php
                        $statusClass = match($order->status->value) {
                            'planned'     => 'badge-gray',
                            'in_progress' => 'badge-blue',
                            'finished'    => 'badge-green',
                            'cancelled'   => 'badge-red',
                            default       => 'badge-gray',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $order->status->label() }}</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Producto</p>
                        <p class="font-medium text-gray-900">{{ $order->product->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Receta</p>
                        <p class="font-medium text-gray-900">{{ $order->recipe->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Almacén</p>
                        <p class="font-medium text-gray-900">{{ $order->warehouse->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Cantidad planificada</p>
                        <p class="font-medium text-gray-900">{{ number_format($order->planned_quantity, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Cantidad producida</p>
                        <p class="font-medium {{ $order->produced_quantity >= $order->planned_quantity ? 'text-emerald-600' : 'text-gray-900' }}">
                            {{ number_format($order->produced_quantity ?? 0, 2) }}
                        </p>
                    </div>
                    @if($order->assignedUser)
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Asignado a</p>
                            <p class="font-medium text-gray-900">{{ $order->assignedUser->name }}</p>
                        </div>
                    @endif
                </div>

                {{-- Barra de progreso --}}
                @if($order->planned_quantity > 0)
                    @php
                        $progress = min(100, round(($order->produced_quantity ?? 0) / $order->planned_quantity * 100));
                    @endphp
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Progreso de producción</span>
                            <span>{{ $progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $progress >= 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                @endif

                @if($order->notes)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Notas</p>
                        <p class="text-sm text-gray-700">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Materiales consumidos --}}
            @if($order->materials && $order->materials->count() > 0)
                <div class="card">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Materiales consumidos</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left py-2 pr-4 text-xs font-medium text-gray-500 uppercase">Material</th>
                                    <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Planif.</th>
                                    <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Real</th>
                                    <th class="text-right py-2 pl-4 text-xs font-medium text-gray-500 uppercase">Variación</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($order->materials as $material)
                                    @php
                                        $variance = ($material->actual_quantity ?? 0) - ($material->planned_quantity ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="py-3 pr-4">
                                            <p class="font-medium text-gray-900">{{ $material->product->name ?? '—' }}</p>
                                        </td>
                                        <td class="py-3 px-4 text-right text-gray-700">{{ number_format($material->planned_quantity ?? 0, 3) }}</td>
                                        <td class="py-3 px-4 text-right text-gray-700">{{ number_format($material->actual_quantity ?? 0, 3) }}</td>
                                        <td class="py-3 pl-4 text-right font-medium {{ $variance > 0 ? 'text-red-600' : ($variance < 0 ? 'text-emerald-600' : 'text-gray-400') }}">
                                            {{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 3) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>

        {{-- Sidebar: costos --}}
        <div class="space-y-6">

            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Costos estimados</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Materiales</span>
                        <span class="font-medium">S/ {{ number_format($order->estimated_material_cost ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Mano de obra</span>
                        <span class="font-medium">S/ {{ number_format($order->estimated_labor_cost ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">G. generales</span>
                        <span class="font-medium">S/ {{ number_format($order->estimated_overhead_cost ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-100">
                        <span class="font-semibold text-gray-900">Total estimado</span>
                        <span class="font-bold text-gray-900">S/ {{ number_format($order->estimated_total_cost ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            @if(($order->actual_total_cost ?? 0) > 0)
                <div class="card">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Costos reales</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Materiales</span>
                            <span class="font-medium">S/ {{ number_format($order->actual_material_cost ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Mano de obra</span>
                            <span class="font-medium">S/ {{ number_format($order->actual_labor_cost ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">G. generales</span>
                            <span class="font-medium">S/ {{ number_format($order->actual_overhead_cost ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-100">
                            <span class="font-semibold text-gray-900">Total real</span>
                            <span class="font-bold text-gray-900">S/ {{ number_format($order->actual_total_cost ?? 0, 2) }}</span>
                        </div>
                        @php
                            $variance = ($order->actual_total_cost ?? 0) - ($order->estimated_total_cost ?? 0);
                        @endphp
                        <div class="flex justify-between pt-2 border-t border-gray-100 {{ $variance > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                            <span class="font-medium">Variación</span>
                            <span class="font-bold">{{ $variance > 0 ? '+' : '' }}S/ {{ number_format($variance, 2) }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Información</h3>
                <div class="space-y-3 text-sm">
                    @if($order->createdBy)
                        <div>
                            <p class="text-xs text-gray-400">Creado por</p>
                            <p class="font-medium text-gray-900">{{ $order->createdBy->name }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-400">Fecha creación</p>
                        <p class="font-medium text-gray-900">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($order->started_at)
                        <div>
                            <p class="text-xs text-gray-400">Inicio real</p>
                            <p class="font-medium text-gray-900">{{ $order->started_at->format('d/m/Y') }}</p>
                        </div>
                    @endif
                    @if($order->finished_at)
                        <div>
                            <p class="text-xs text-gray-400">Fin real</p>
                            <p class="font-medium text-gray-900">{{ $order->finished_at->format('d/m/Y') }}</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
