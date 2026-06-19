<div>
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('purchases.index') }}" class="hover:text-gray-700">Compras</a>
                <span>/</span>
                <a href="{{ route('purchases.show', $order) }}" class="hover:text-gray-700">{{ $order->order_number }}</a>
                <span>/</span>
                <span>Recepción</span>
            </div>
            <h1 class="page-title">Recepción de Mercancía</h1>
            <p class="text-sm text-gray-500 mt-1">
                Orden {{ $order->order_number }} —
                {{ $order->supplier->display_name ?? 'Proveedor' }}
            </p>
        </div>
        <a href="{{ route('purchases.show', $order) }}" class="btn-secondary">Cancelar</a>
    </div>

    @if($errors->has('receiving'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
            {{ $errors->first('receiving') }}
        </div>
    @endif

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                <div class="card">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Datos de recepción</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Fecha de recepción <span class="text-red-500">*</span></label>
                            <input wire:model="receivedDate" type="date" class="form-input">
                            @error('receivedDate') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Almacén destino</label>
                            <p class="form-input bg-gray-50 text-gray-600 cursor-default">{{ $order->warehouse->name ?? '—' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Notas de recepción</label>
                            <textarea wire:model="notes" rows="2" class="form-input" placeholder="Observaciones sobre la entrega…"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Tabla de ítems a recibir --}}
                <div class="card">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">
                        Ítems a recibir
                        <span class="text-gray-400 font-normal">(ingresa la cantidad que llega ahora)</span>
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left py-2 pr-4 text-xs font-medium text-gray-500 uppercase">Producto</th>
                                    <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Pedido</th>
                                    <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Ya recibido</th>
                                    <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Pendiente</th>
                                    <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Recibir ahora</th>
                                    <th class="text-right py-2 pl-4 text-xs font-medium text-gray-500 uppercase">Costo unit.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($receiving as $itemId => $rec)
                                    <tr>
                                        <td class="py-4 pr-4">
                                            <p class="font-medium text-gray-900">{{ $rec['product_name'] }}</p>
                                        </td>
                                        <td class="py-4 px-4 text-right text-gray-600">
                                            {{ number_format($rec['ordered'], 2) }}
                                        </td>
                                        <td class="py-4 px-4 text-right text-emerald-600">
                                            {{ number_format($rec['received'], 2) }}
                                        </td>
                                        <td class="py-4 px-4 text-right {{ $rec['pending'] > 0 ? 'text-amber-600 font-medium' : 'text-gray-400' }}">
                                            {{ number_format($rec['pending'], 2) }}
                                        </td>
                                        <td class="py-4 px-4">
                                            @if($rec['pending'] > 0)
                                                <input wire:model="receiving.{{ $itemId }}.now"
                                                       type="number" step="0.001" min="0" max="{{ $rec['pending'] }}"
                                                       class="form-input text-right w-24 ml-auto">
                                            @else
                                                <span class="block text-right text-gray-400 pr-1">Completo</span>
                                            @endif
                                        </td>
                                        <td class="py-4 pl-4">
                                            <input wire:model="receiving.{{ $itemId }}.unit_cost"
                                                   type="number" step="0.0001" min="0"
                                                   class="form-input text-right w-28 ml-auto">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                <div class="card bg-indigo-50 border border-indigo-100">
                    <h3 class="text-sm font-semibold text-indigo-900 mb-3">Resumen de la orden</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-indigo-700">Proveedor</span>
                            <span class="font-medium text-indigo-900">{{ $order->supplier->display_name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-indigo-700">Nro. orden</span>
                            <span class="font-medium text-indigo-900">{{ $order->order_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-indigo-700">Total ítems</span>
                            <span class="font-medium text-indigo-900">{{ count($receiving) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-indigo-700">Total orden</span>
                            <span class="font-medium text-indigo-900">{{ $order->currency ?? 'S/' }} {{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <p class="text-xs text-gray-500 mb-4">
                        Al confirmar, se registrarán movimientos de entrada al almacén
                        <strong>{{ $order->warehouse->name ?? '' }}</strong>
                        y se actualizará el stock de los productos.
                    </p>
                    <div class="flex flex-col gap-2">
                        <button type="submit" class="btn-primary w-full">
                            <span wire:loading.remove>Confirmar recepción</span>
                            <span wire:loading>Procesando…</span>
                        </button>
                        <a href="{{ route('purchases.show', $order) }}" class="btn-secondary w-full text-center">
                            Cancelar
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
