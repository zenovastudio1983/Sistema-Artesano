<div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Recuperar contraseña</h3>
    <p class="text-sm text-gray-500 mb-6">
        Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.
    </p>

    @if($sent)
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            Si ese correo está registrado, recibirás un enlace en los próximos minutos.
        </div>
    @else
        <form wire:submit="sendLink" class="space-y-5">
            <div>
                <label for="email" class="form-label">Correo electrónico</label>
                <input
                    wire:model="email"
                    id="email"
                    type="email"
                    autocomplete="email"
                    autofocus
                    class="form-input @error('email') border-red-500 @enderror"
                    placeholder="tu@correo.com"
                />
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary w-full justify-center py-2.5">
                <span wire:loading.remove>Enviar enlace</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="spinner w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Enviando...
                </span>
            </button>
        </form>
    @endif

    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium">
            &larr; Volver al inicio de sesión
        </a>
    </div>
</div>
