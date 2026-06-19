<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Órdenes de Producción</h1>
            <p class="text-sm text-gray-500 mt-1">Gestión del flujo de fabricación</p>
        </div>
        @can('create production orders')
        <a href="{{ route('production.create') }}"
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva Orden
        </a>
        @endcan
    </div>

    {{-- Status pills --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <button wire:click="$set('filterStatus', '')"
                class="px-3 py-1.5 text-xs font-medium rounded-full transition-colors
                       {{ $filterStatus === '' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            Todas ({{ array_sum($statusCounts->toArray()) }})
        </button>
        @foreach($statuses as $status)
        <button wire:click="$set('filterStatus', '{{ $status->value }}')"
                class="px-3 py-1.5 text-xs font-medium rounded-full transition-colors border
                       {{ $filterStatus === $status->value ? 'ring-2 ring-offset-1' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}
                       {{ match($status->color()) {
                           'gray' => 'border-gray-300 text-gray-700',
                           'blue' => 'border-blue-300 text-blue-700 bg-blue-50',
                           'yellow' => 'border-yellow-300 text-yellow-700 bg-yellow-50',
                           'green' => 'border-green-300 text-green-700 bg-green-50',
                           'red' => 'border-red-300 text-red-700 bg-red-50',
                           default => 'border-gray-200 text-gray-600',
                       } }}">
            {{ $status->label() }} ({{ $statusCounts[$status->value] ?? 0 }})
        </button>
        @endforeach
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search"
                   type="text"
                   placeholder="Buscar por número de orden o producto..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Orden / Producto</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Planificado</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Producido</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Costo Est.</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha Plan.</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <a href="{{ route('production.show', $order) }}"
                                   class="text-sm font-medium text-indigo-600 hover:text-indigo-800 font-mono">
                                    {{ $order->order_number }}
                                </a>
                                <p class="text-sm text-gray-900 mt-0.5">{{ $order->product->name }}</p>
                                <p class="text-xs text-gray-400">{{ $order->warehouse->name }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ match($order->status->color()) {
                                    'gray' => 'bg-gray-100 text-gray-700',
                                    'blue' => 'bg-blue-100 text-blue-700',
                                    'yellow' => 'bg-yellow-100 text-yellow-700',
                                    'green' => 'bg-green-100 text-green-700',
                                    'red' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-700',
                                } }}">
                                {{ $order->status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-sm text-gray-700">
                            {{ number_format($order->planned_quantity, 2) }}
                            <span class="text-xs text-gray-400">{{ $order->product->unit }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="text-right">
                                <span class="font-mono text-sm {{ $order->produced_quantity > 0 ? 'text-green-700 font-semibold' : 'text-gray-400' }}">
                                    {{ number_format($order->produced_quantity, 2) }}
                                </span>
                                @if($order->planned_quantity > 0)
                                <div class="mt-1 w-full bg-gray-100 rounded-full h-1">
                                    <div class="bg-green-500 h-1 rounded-full"
                                         style="width: {{ min(100, $order->progress_percent) }}%"></div>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-sm text-gray-700">
                            {{ config('erp.currency_symbol') }} {{ number_format($order->estimated_total_cost, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($order->planned_start_date)
                                {{ $order->planned_start_date->format('d/m/Y') }}
                                @if($order->planned_end_date)
                                    <span class="text-gray-400">→ {{ $order->planned_end_date->format('d/m/Y') }}</span>
                                @endif
                            @else
                                <span class="text-gray-300">Sin fecha</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-1">
                                @if($order->status->canTransitionTo(\App\Support\Enums\ProductionOrderStatus::Planned))
                                <button wire:click="openTransitionModal({{ $order->id }}, 'planned')"
                                        class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded transition-colors">
                                    Planificar
                                </button>
                                @endif
                                @if($order->status->canTransitionTo(\App\Support\Enums\ProductionOrderStatus::InProgress))
                                <button wire:click="openTransitionModal({{ $order->id }}, 'in_progress')"
                                        class="px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100 rounded transition-colors">
                                    Iniciar
                                </button>
                                @endif
                                @if($order->status->canTransitionTo(\App\Support\Enums\ProductionOrderStatus::Finished))
                                <button wire:click="openTransitionModal({{ $order->id }}, 'finished')"
                                        class="px-2 py-1 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded transition-colors">
                                    Finalizar
                                </button>
                                @endif
                                <a href="{{ route('production.show', $order) }}"
                                   class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                            <svg class="mx-auto w-12 h-12 text-gray-200 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            </svg>
                            No se encontraron órdenes de producción
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    {{-- Transition modal --}}
    @if($showTransitionModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showTransitionModal', false)"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Cambiar estado → {{ \App\Support\Enums\ProductionOrderStatus::from($targetStatus)->label() }}
                </h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas (opcional)</label>
                    <textarea wire:model="transitionNotes" rows="3"
                              placeholder="Observaciones sobre el cambio de estado..."
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex space-x-3 justify-end">
                    <button wire:click="$set('showTransitionModal', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button wire:click="executeTransition"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
