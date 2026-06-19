<div>
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('purchases.index') }}" class="hover:text-gray-700">Compras</a>
                <span>/</span>
                <span>{{ $order->order_number }}</span>
            </div>
            <h1 class="page-title">Orden de Compra {{ $order->order_number }}</h1>
        </div>
        <div class="flex gap-2">
            @if($order->status->value === 'draft')
                <a href="{{ route('purchases.edit', $order) }}" class="btn-secondary">Editar</a>
                <a href="{{ route('purchases.receive', $order) }}" class="btn-primary">Recibir Mercancía</a>
            @elseif($order->status->value === 'partially_received')
                <a href="{{ route('purchases.receive', $order) }}" class="btn-primary">Continuar Recepción</a>
            @endif
            <a href="{{ route('purchases.index') }}" class="btn-secondary">Volver</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Columna principal --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Cabecera --}}
            <div class="card">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $order->order_number }}</h2>
                        <p class="text-sm text-gray-500">{{ $order->order_date->format('d/m/Y') }}</p>
                    </div>
                    @php
                        $statusClass = match($order->status->value) {
                            'draft'              => 'badge-gray',
                            'sent'               => 'badge-blue',
                            'partially_received' => 'badge-yellow',
                            'received'           => 'badge-green',
                            'cancelled'          => 'badge-red',
                            default              => 'badge-gray',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $order->status->label() }}</span>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Proveedor</p>
                        <p class="font-medium text-gray-900">{{ $order->supplier->display_name ?? '—' }}</p>
                        @if($order->supplier?->tax_id)
                            <p class="text-sm text-gray-500">{{ $order->supplier->tax_id }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Almacén destino</p>
                        <p class="font-medium text-gray-900">{{ $order->warehouse->name ?? '—' }}</p>
                    </div>
                    @if($order->expected_date)
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Fecha esperada</p>
                            <p class="font-medium text-gray-900">{{ $order->expected_date->format('d/m/Y') }}</p>
                        </div>
                    @endif
                    @if($order->received_date)
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Fecha recepción</p>
                            <p class="font-medium text-gray-900">{{ $order->received_date->format('d/m/Y') }}</p>
                        </div>
                    @endif
                    @if($order->reference)
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Referencia</p>
                            <p class="font-medium text-gray-900">{{ $order->reference }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Moneda</p>
                        <p class="font-medium text-gray-900">{{ $order->currency ?? 'PEN' }}</p>
                    </div>
                </div>

                @if($order->notes)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Notas</p>
                        <p class="text-sm text-gray-700">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Ítems --}}
            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Ítems del pedido</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-2 pr-4 text-xs font-medium text-gray-500 uppercase">Producto</th>
                                <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Pedido</th>
                                <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Recibido</th>
                                <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Pendiente</th>
                                <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Precio unit.</th>
                                <th class="text-right py-2 pl-4 text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($order->items as $item)
                                @php
                                    $pending = max(0, $item->quantity - $item->received_quantity);
                                @endphp
                                <tr>
                                    <td class="py-3 pr-4">
                                        <p class="font-medium text-gray-900">{{ $item->product->name ?? '—' }}</p>
                                        @if($item->description)
                                            <p class="text-xs text-gray-400">{{ $item->description }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right text-gray-700">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="py-3 px-4 text-right text-emerald-600 font-medium">{{ number_format($item->received_quantity, 2) }}</td>
                                    <td class="py-3 px-4 text-right {{ $pending > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                        {{ number_format($pending, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-gray-700">
                                        {{ $order->currency ?? 'S/' }} {{ number_format($item->unit_price, 2) }}
                                        @if($item->discount_percent > 0)
                                            <span class="text-xs text-gray-400 block">-{{ $item->discount_percent }}% dto.</span>
                                        @endif
                                    </td>
                                    <td class="py-3 pl-4 text-right font-medium text-gray-900">
                                        {{ $order->currency ?? 'S/' }} {{ number_format($item->subtotal, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Sidebar: totales + meta --}}
        <div class="space-y-6">

            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Totales</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-medium">{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->tax_rate > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-500">IGV ({{ $order->tax_rate }}%)</span>
                            <span class="font-medium">{{ number_format($order->tax_amount, 2) }}</span>
                        </div>
                    @endif
                    @if($order->shipping_cost > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Flete</span>
                            <span class="font-medium">{{ number_format($order->shipping_cost, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-gray-100">
                        <span class="font-semibold text-gray-900">Total</span>
                        <span class="font-bold text-lg text-gray-900">{{ $order->currency ?? 'S/' }} {{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

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
                    @if($order->approvedBy)
                        <div>
                            <p class="text-xs text-gray-400">Aprobado por</p>
                            <p class="font-medium text-gray-900">{{ $order->approvedBy->name }}</p>
                        </div>
                    @endif
                    @if($order->updated_at != $order->created_at)
                        <div>
                            <p class="text-xs text-gray-400">Última modificación</p>
                            <p class="font-medium text-gray-900">{{ $order->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
