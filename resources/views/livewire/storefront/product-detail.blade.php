<div x-data>
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="{{ route('tienda.index') }}" class="hover:text-gray-600 transition-colors">Catálogo</a>
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">

        {{-- Imagen principal --}}
        <div class="aspect-square bg-gray-50 rounded-2xl overflow-hidden shadow-sm">
            @if($product->image_url)
                <img src="{{ $product->image_url }}"
                     alt="{{ $product->name }}"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-50 to-purple-50">
                    <span class="text-9xl font-black text-indigo-100 select-none">
                        {{ strtoupper(mb_substr($product->name, 0, 1)) }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Información del producto --}}
        <div class="flex flex-col">

            {{-- Badge de disponibilidad --}}
            @php
                $stock = (float) $product->total_stock;
                $available = $product->is_made_to_order || $stock > 0;
            @endphp

            @if($product->is_made_to_order)
                <span class="inline-flex items-center gap-1.5 w-fit bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-full mb-4">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    A pedido
                    @if($product->lead_time_days)
                        · Entrega en {{ $product->lead_time_days }} días
                    @endif
                </span>
            @elseif($stock <= 0)
                <span class="inline-flex items-center gap-1.5 w-fit bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full mb-4">
                    Sin stock
                </span>
            @elseif($stock <= 5)
                <span class="inline-flex items-center gap-1.5 w-fit bg-orange-100 text-orange-700 text-xs font-bold px-3 py-1 rounded-full mb-4">
                    Últimas {{ (int) $stock }} unidades
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 w-fit bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full mb-4">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Disponible · {{ (int) $stock }} en stock
                </span>
            @endif

            {{-- Nombre --}}
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight">
                {{ $product->name }}
            </h1>

            {{-- Precio --}}
            <p class="text-3xl font-bold text-indigo-700 mt-4">
                {{ config('erp.currency_symbol') }} {{ number_format((float) $product->price, 2) }}
            </p>

            {{-- Descripción pública o interna --}}
            @php
                $desc = $product->public_description ?: $product->description;
            @endphp
            @if($desc)
                <p class="text-gray-600 text-sm leading-relaxed mt-4">
                    {{ $desc }}
                </p>
            @endif

            {{-- Botón agregar al carrito --}}
            @if($available)
                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <button
                        @click="$store.cart.add({
                            id:              {{ $product->id }},
                            name:            @js($product->name),
                            price:           {{ (float) $product->price }},
                            stock:           {{ $stock }},
                            is_made_to_order:{{ $product->is_made_to_order ? 'true' : 'false' }},
                            image:           @js($product->image_url ?? '')
                        })"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold py-3 px-6 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Agregar al carrito
                    </button>
                    <a href="{{ route('tienda.index') }}"
                       class="px-5 py-3 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition-colors text-sm font-medium text-center">
                        ← Ver más
                    </a>
                </div>
            @else
                <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <p class="text-gray-500 text-sm text-center">Este producto no está disponible actualmente</p>
                </div>
                <a href="{{ route('tienda.index') }}" class="mt-3 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors text-center">
                    ← Volver al catálogo
                </a>
            @endif

            {{-- Info adicional: datos a pedido --}}
            @if($product->is_made_to_order)
                <div class="mt-5 p-4 bg-amber-50 border border-amber-100 rounded-xl">
                    <p class="text-xs font-semibold text-amber-800 mb-1">Producto a pedido</p>
                    <p class="text-xs text-amber-700">
                        Este producto se elabora especialmente para vos.
                        @if($product->lead_time_days)
                            El tiempo de entrega estimado es de <strong>{{ $product->lead_time_days }} días</strong>.
                        @endif
                        Una vez recibido el pedido por WhatsApp, nos comunicaremos para coordinar los detalles.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
