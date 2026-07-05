<div>
    {{-- Hero --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Catálogo</h1>
        <p class="text-gray-500 mt-1 text-sm">Todos nuestros productos artesanales</p>
    </div>

    {{-- Búsqueda --}}
    <div class="mb-6">
        <div class="relative max-w-md">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.350ms="search"
                   type="text"
                   placeholder="Buscar productos…"
                   class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-white
                          focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent shadow-sm">
            <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="animate-spin w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Grilla de productos --}}
    @if($products->isEmpty())
        <div class="text-center py-20">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            @if($search)
                <p class="text-gray-600 font-medium">Sin resultados para "{{ $search }}"</p>
                <p class="text-gray-400 text-sm mt-1">Probá con otro término</p>
            @else
                <p class="text-gray-600 font-medium">No hay productos disponibles</p>
                <p class="text-gray-400 text-sm mt-1">Volvé más tarde</p>
            @endif
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
            @foreach($products as $product)
            @php
                $available = $product->is_made_to_order || $product->total_stock > 0;
                $stock     = (float) $product->total_stock;
            @endphp
            <div x-data class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition-all duration-200 group border border-gray-50">

                {{-- Imagen --}}
                <a href="{{ route('tienda.product', $product->public_slug) }}"
                   class="block aspect-square bg-gray-50 overflow-hidden relative">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}"
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-50 to-purple-50">
                            <span class="text-5xl font-black text-indigo-100 select-none">{{ strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                        </div>
                    @endif

                    {{-- Badges --}}
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        @if($product->is_made_to_order)
                            <span class="bg-amber-400 text-amber-900 text-xs font-bold px-2 py-0.5 rounded-full shadow-sm">A pedido</span>
                        @elseif($stock <= 0)
                            <span class="bg-gray-700/80 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-sm backdrop-blur-sm">Sin stock</span>
                        @elseif($stock <= 5)
                            <span class="bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-sm">Últimos {{ (int) $stock }}</span>
                        @endif
                    </div>
                </a>

                {{-- Info --}}
                <div class="p-3 sm:p-4">
                    <a href="{{ route('tienda.product', $product->public_slug) }}">
                        <h3 class="text-sm font-semibold text-gray-900 leading-snug hover:text-indigo-700 transition-colors line-clamp-2">
                            {{ $product->name }}
                        </h3>
                    </a>

                    @if($product->is_made_to_order && $product->lead_time_days)
                        <p class="text-xs text-amber-600 mt-0.5">Entrega en {{ $product->lead_time_days }} días</p>
                    @endif

                    <p class="text-base font-bold text-indigo-700 mt-1.5">
                        {{ config('erp.currency_symbol') }} {{ number_format((float) $product->price, 2) }}
                    </p>

                    @if($available)
                        <button
                            @click="$store.cart.add({
                                id:              {{ $product->id }},
                                name:            @js($product->name),
                                price:           {{ (float) $product->price }},
                                stock:           {{ $stock }},
                                is_made_to_order:{{ $product->is_made_to_order ? 'true' : 'false' }},
                                image:           @js($product->image_url ?? '')
                            })"
                            class="mt-2 w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold py-2 rounded-lg transition-colors">
                            + Agregar
                        </button>
                    @else
                        <button disabled
                                class="mt-2 w-full bg-gray-100 text-gray-400 text-sm font-medium py-2 rounded-lg cursor-not-allowed">
                            Sin stock
                        </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
