<div
    x-data
    @open-whatsapp.window="window.open($event.detail.url, '_blank')"
>
    <div class="max-w-2xl mx-auto">

        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-3" style="font-family: 'Playfair Display', serif;">
                Contacto
            </h1>
            <div class="w-12 h-1 bg-purple-600 rounded mx-auto mb-4"></div>
            <p class="text-gray-500 text-sm">Escribinos y te respondemos a la brevedad por WhatsApp</p>
        </div>

        @if($sent)
            <div class="bg-green-50 border border-green-200 rounded-2xl p-6 text-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="font-semibold text-green-800">¡Mensaje enviado!</p>
                <p class="text-sm text-green-600 mt-1">Se abrió WhatsApp con tu mensaje listo para enviar.</p>
                <button wire:click="$set('sent', false)" class="mt-4 text-sm text-green-700 underline">
                    Enviar otro mensaje
                </button>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <form wire:submit="send" class="space-y-5">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Tu nombre <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" class="form-input" placeholder="María García">
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Correo electrónico <span class="text-red-500">*</span></label>
                            <input wire:model="email" type="email" class="form-input" placeholder="tu@correo.com">
                            @error('email') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Mensaje <span class="text-red-500">*</span></label>
                        <textarea wire:model="message" rows="5" class="form-input resize-none"
                                  placeholder="Contanos en qué podemos ayudarte…"></textarea>
                        @error('message') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white font-semibold py-3 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            <path d="M11.95 0C5.364 0 0 5.364 0 11.95c0 2.107.555 4.074 1.525 5.775L0 24l6.467-1.496A11.897 11.897 0 0011.95 23.9c6.586 0 11.95-5.364 11.95-11.95S18.536 0 11.95 0zm0 21.853a9.895 9.895 0 01-5.03-1.369l-.362-.214-3.743.866.9-3.638-.236-.374A9.865 9.865 0 012.1 11.95c0-5.43 4.42-9.85 9.85-9.85 5.43 0 9.85 4.42 9.85 9.85 0 5.43-4.42 9.853-9.85 9.853z"/>
                        </svg>
                        <span wire:loading.remove>Enviar por WhatsApp</span>
                        <span wire:loading>Preparando mensaje…</span>
                    </button>

                </form>
            </div>
        @endif

    </div>
</div>
