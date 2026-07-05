<!DOCTYPE html>
<html lang="es" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('erp.company.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script>
        document.addEventListener('alpine:init', () => {
            const WA_NUMBER = @json(config('erp.whatsapp_number', ''));
            const CURRENCY  = @json(config('erp.currency_symbol', '$'));

            Alpine.store('cart', {
                items:  JSON.parse(localStorage.getItem('mta_cart') || '[]'),
                isOpen: false,

                add(product) {
                    const idx = this.items.findIndex(i => i.id === product.id);
                    if (idx >= 0) {
                        if (!product.is_made_to_order && this.items[idx].quantity >= product.stock) return;
                        this.items[idx].quantity++;
                    } else {
                        this.items.push({ ...product, quantity: 1 });
                    }
                    this.save();
                    this.isOpen = true;
                },

                remove(id) {
                    this.items = this.items.filter(i => i.id !== id);
                    this.save();
                },

                updateQty(id, delta) {
                    const item = this.items.find(i => i.id === id);
                    if (!item) return;
                    const next = item.quantity + delta;
                    if (next <= 0) { this.remove(id); return; }
                    if (!item.is_made_to_order && next > item.stock) return;
                    item.quantity = next;
                    this.save();
                },

                save() {
                    localStorage.setItem('mta_cart', JSON.stringify(this.items));
                },

                clear() {
                    this.items = [];
                    this.save();
                },

                get count() {
                    return this.items.reduce((s, i) => s + i.quantity, 0);
                },

                get total() {
                    return this.items.reduce((s, i) => s + i.price * i.quantity, 0);
                },

                sendWhatsapp(name, notes) {
                    let msg = '¡Hola! Quisiera hacer un pedido 😊\n\n';
                    this.items.forEach(i => {
                        const sub = (i.price * i.quantity).toFixed(2);
                        msg += `• ${i.quantity}x ${i.name} — ${CURRENCY} ${sub}\n`;
                        if (i.is_made_to_order) msg += `  _(a pedido)_\n`;
                    });
                    msg += `\n*Total estimado: ${CURRENCY} ${this.total.toFixed(2)}*`;
                    if (name) msg += `\n\nMi nombre: ${name}`;
                    if (notes) msg += `\nNotas: ${notes}`;
                    window.open(`https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(msg)}`, '_blank');
                }
            });
        });
    </script>
</head>
<body class="min-h-full bg-gray-50" style="font-family: 'Inter', sans-serif;">

    {{-- Backdrop del carrito --}}
    <div x-data
         x-show="$store.cart.isOpen"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="$store.cart.isOpen = false"
         class="fixed inset-0 bg-black/40 z-40"
         style="display:none">
    </div>

    {{-- Panel lateral del carrito --}}
    <div x-data="{ customerName: '', customerNotes: '' }"
         x-show="$store.cart.isOpen"
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed right-0 top-0 h-full w-full max-w-sm bg-white shadow-2xl z-50 flex flex-col"
         style="display:none">

        {{-- Encabezado --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h2 class="font-semibold text-gray-900">Tu carrito</h2>
                <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-0.5 rounded-full"
                      x-text="$store.cart.count + ($store.cart.count === 1 ? ' ítem' : ' ítems')"></span>
            </div>
            <button @click="$store.cart.isOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Lista de ítems --}}
        <div class="flex-1 overflow-y-auto px-5 py-4">

            <template x-if="$store.cart.items.length === 0">
                <div class="flex flex-col items-center justify-center h-full text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">Tu carrito está vacío</p>
                    <p class="text-gray-400 text-xs mt-1">Agregá productos del catálogo</p>
                    <button @click="$store.cart.isOpen = false" class="mt-4 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                        Ver catálogo →
                    </button>
                </div>
            </template>

            <template x-if="$store.cart.items.length > 0">
                <div class="space-y-4">
                    <template x-for="item in $store.cart.items" :key="item.id">
                        <div class="flex gap-3 py-2">
                            <div class="w-16 h-16 bg-gray-50 rounded-xl overflow-hidden flex-shrink-0 border border-gray-100">
                                <template x-if="item.image">
                                    <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.image">
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-50 to-purple-50">
                                        <span class="text-xl font-black text-indigo-200"
                                              x-text="item.name.charAt(0).toUpperCase()"></span>
                                    </div>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 leading-snug truncate" x-text="item.name"></p>
                                <p class="text-xs text-amber-600 mt-0.5" x-show="item.is_made_to_order">A pedido</p>
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center gap-1.5">
                                        <button @click="$store.cart.updateQty(item.id, -1)"
                                                class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:border-gray-300 text-base font-medium transition-colors">
                                            −
                                        </button>
                                        <span class="text-sm font-bold w-6 text-center tabular-nums" x-text="item.quantity"></span>
                                        <button @click="$store.cart.updateQty(item.id, 1)"
                                                class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:border-gray-300 text-base font-medium transition-colors">
                                            +
                                        </button>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-bold text-gray-900 tabular-nums"
                                              x-text="'{{ config('erp.currency_symbol') }} ' + (item.price * item.quantity).toFixed(2)"></span>
                                        <button @click="$store.cart.remove(item.id)"
                                                class="block text-xs text-red-400 hover:text-red-600 mt-0.5 transition-colors">
                                            Quitar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        {{-- Zona de checkout --}}
        <template x-if="$store.cart.items.length > 0">
            <div class="border-t border-gray-100 px-5 py-4 space-y-3 flex-shrink-0 bg-gray-50/80">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-semibold text-gray-700">Total estimado</span>
                    <span class="text-lg font-bold text-gray-900 tabular-nums"
                          x-text="'{{ config('erp.currency_symbol') }} ' + $store.cart.total.toFixed(2)"></span>
                </div>
                <input x-model="customerName"
                       type="text"
                       placeholder="Tu nombre (requerido)"
                       class="w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                <textarea x-model="customerNotes"
                          rows="2"
                          placeholder="Notas u observaciones (opcional)"
                          class="w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent resize-none"></textarea>
                <button @click="
                    if (!customerName.trim()) {
                        alert('Por favor ingresá tu nombre antes de enviar el pedido.');
                        return;
                    }
                    $store.cart.sendWhatsapp(customerName, customerNotes);
                " class="w-full flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 active:bg-green-700 text-white font-semibold py-3 rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                        <path d="M11.95 0C5.364 0 0 5.364 0 11.95c0 2.107.555 4.074 1.525 5.775L0 24l6.467-1.496A11.897 11.897 0 0011.95 23.9c6.586 0 11.95-5.364 11.95-11.95S18.536 0 11.95 0zm0 21.853a9.895 9.895 0 01-5.03-1.369l-.362-.214-3.743.866.9-3.638-.236-.374A9.865 9.865 0 012.1 11.95c0-5.43 4.42-9.85 9.85-9.85 5.43 0 9.85 4.42 9.85 9.85 0 5.43-4.42 9.853-9.85 9.853z"/>
                    </svg>
                    Enviar pedido por WhatsApp
                </button>
                <button @click="if (confirm('¿Vaciar el carrito?')) $store.cart.clear()"
                        class="w-full text-xs text-gray-400 hover:text-red-500 py-1 transition-colors text-center">
                    Vaciar carrito
                </button>
            </div>
        </template>
    </div>

    {{-- Header fijo --}}
    <header class="bg-white border-b border-gray-100 sticky top-0 z-30 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('tienda.index') }}" class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <span class="font-bold text-gray-900 text-sm truncate">{{ config('erp.company.name') }}</span>
            </a>

            {{-- Botón carrito --}}
            <div x-data>
                <button @click="$store.cart.isOpen = true"
                        class="relative flex items-center gap-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-2 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="text-sm font-semibold hidden sm:inline">Carrito</span>
                    <template x-if="$store.cart.count > 0">
                        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold"
                              x-text="$store.cart.count"></span>
                    </template>
                </button>
            </div>
        </div>
    </header>

    {{-- Contenido principal --}}
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-[calc(100vh-4rem-5rem)]">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-100 mt-8 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between">
            <p class="text-xs text-gray-400">© {{ date('Y') }} {{ config('erp.company.name') }}</p>
            <a href="{{ route('login') }}"
               class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                Acceso empleados →
            </a>
        </div>
    </footer>

    @livewireScripts
    <script>
        window.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (data) => {
                window.dispatchEvent(new CustomEvent('notify', { detail: data[0] ?? data }));
            });
        });
    </script>
</body>
</html>
