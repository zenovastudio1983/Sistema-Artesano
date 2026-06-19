<div>
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('sales.index') }}" class="hover:text-gray-700">Ventas</a>
                <span>/</span>
                <span>{{ $sale->sale_number }}</span>
            </div>
            <h1 class="page-title">Venta {{ $sale->sale_number }}</h1>
        </div>
        <div class="flex gap-2">
            @if(in_array($sale->status->value, ['quotation', 'confirmed']))
                <a href="{{ route('sales.edit', $sale) }}" class="btn-secondary">Editar</a>
            @endif
            <a href="{{ route('sales.index') }}" class="btn-secondary">Volver</a>
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
                        <h2 class="text-lg font-semibold text-gray-900">{{ $sale->sale_number }}</h2>
                        <p class="text-sm text-gray-500">{{ $sale->sale_date->format('d/m/Y') }}</p>
                    </div>
                    @php
                        $statusClass = match($sale->status->value) {
                            'quotation' => 'badge-gray',
                            'confirmed' => 'badge-blue',
                            'invoiced'  => 'badge-yellow',
                            'paid'      => 'badge-green',
                            'cancelled' => 'badge-red',
                            default     => 'badge-gray',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $sale->status->label() }}</span>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Cliente</p>
                        <p class="font-medium text-gray-900">{{ $sale->customer?->display_name ?? 'Cliente general' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Almacén</p>
                        <p class="font-medium text-gray-900">{{ $sale->warehouse?->name ?? '—' }}</p>
                    </div>
                    @if($sale->due_date)
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Vencimiento</p>
                            <p class="font-medium text-gray-900">{{ $sale->due_date->format('d/m/Y') }}</p>
                        </div>
                    @endif
                    @if($sale->seller)
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Vendedor</p>
                            <p class="font-medium text-gray-900">{{ $sale->seller->name }}</p>
                        </div>
                    @endif
                    @if($sale->reference)
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Referencia</p>
                            <p class="font-medium text-gray-900">{{ $sale->reference }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Moneda</p>
                        <p class="font-medium text-gray-900">{{ $sale->currency ?? 'PEN' }}</p>
                    </div>
                </div>

                @if($sale->notes)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Notas</p>
                        <p class="text-sm text-gray-700">{{ $sale->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Ítems --}}
            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Ítems de la venta</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-2 pr-4 text-xs font-medium text-gray-500 uppercase">Producto</th>
                                <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Cant.</th>
                                <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Precio</th>
                                <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Descuento</th>
                                <th class="text-right py-2 px-4 text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                <th class="text-right py-2 pl-4 text-xs font-medium text-gray-500 uppercase">Margen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($sale->items as $item)
                                <tr>
                                    <td class="py-3 pr-4">
                                        <p class="font-medium text-gray-900">{{ $item->product->name ?? '—' }}</p>
                                        @if($item->description)
                                            <p class="text-xs text-gray-400">{{ $item->description }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right text-gray-700">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="py-3 px-4 text-right text-gray-700">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="py-3 px-4 text-right text-gray-500">
                                        {{ $item->discount_percent > 0 ? $item->discount_percent . '%' : '—' }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium text-gray-900">{{ number_format($item->subtotal, 2) }}</td>
                                    <td class="py-3 pl-4 text-right">
                                        @if(isset($item->margin) && $item->margin !== null)
                                            <span class="{{ $item->margin >= 0 ? 'text-emerald-600' : 'text-red-600' }} font-medium">
                                                {{ number_format($item->margin, 1) }}%
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
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

            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Totales</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-medium">{{ number_format($sale->subtotal, 2) }}</span>
                    </div>
                    @if($sale->discount_percent > 0)
                        <div class="flex justify-between text-amber-600">
                            <span>Descuento ({{ $sale->discount_percent }}%)</span>
                            <span class="font-medium">-{{ number_format($sale->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    @if($sale->tax_rate > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-500">IGV ({{ $sale->tax_rate }}%)</span>
                            <span class="font-medium">{{ number_format($sale->tax_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-gray-100">
                        <span class="font-semibold text-gray-900">Total</span>
                        <span class="font-bold text-lg text-gray-900">{{ $sale->currency ?? 'S/' }} {{ number_format($sale->total, 2) }}</span>
                    </div>
                    @if(isset($sale->gross_profit))
                        <div class="flex justify-between pt-2 border-t border-gray-100 text-emerald-700">
                            <span class="font-medium">Margen bruto</span>
                            <span class="font-bold">{{ number_format($sale->gross_profit, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Información</h3>
                <div class="space-y-3 text-sm">
                    @if($sale->createdBy)
                        <div>
                            <p class="text-xs text-gray-400">Creado por</p>
                            <p class="font-medium text-gray-900">{{ $sale->createdBy->name }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-400">Fecha creación</p>
                        <p class="font-medium text-gray-900">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($sale->updated_at != $sale->created_at)
                        <div>
                            <p class="text-xs text-gray-400">Última modificación</p>
                            <p class="font-medium text-gray-900">{{ $sale->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
