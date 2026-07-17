<div>
    <div class="max-w-md mx-auto">

        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block">
                <h1 class="font-bold text-2xl text-gray-900" style="font-family: 'Playfair Display', serif;">ArtEmAr</h1>
            </a>
            <h2 class="text-lg font-semibold text-gray-800 mt-4">Crear cuenta</h2>
            <p class="text-sm text-gray-500 mt-1">Registrate para hacer tus pedidos</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <form wire:submit="register" class="space-y-4">

                <div>
                    <label class="form-label">Tu nombre <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="form-input" placeholder="María García" autocomplete="name">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Correo electrónico <span class="text-red-500">*</span></label>
                    <input wire:model="email" type="email" class="form-input" placeholder="tu@correo.com" autocomplete="email">
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Teléfono / WhatsApp <span class="text-gray-400 font-normal">(opcional)</span></label>
                    <input wire:model="phone" type="tel" class="form-input" placeholder="+54 9 11 1234-5678">
                </div>

                <div>
                    <label class="form-label">Contraseña <span class="text-red-500">*</span></label>
                    <input wire:model="password" type="password" class="form-input" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Repetir contraseña <span class="text-red-500">*</span></label>
                    <input wire:model="passwordConfirmation" type="password" class="form-input" placeholder="Repetí tu contraseña" autocomplete="new-password">
                    @error('passwordConfirmation') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                        class="w-full bg-purple-700 hover:bg-purple-800 text-white font-semibold py-3 rounded-xl transition-colors mt-2">
                    <span wire:loading.remove>Crear cuenta</span>
                    <span wire:loading>Creando cuenta…</span>
                </button>

            </form>
        </div>

        <p class="text-center text-sm text-gray-500 mt-5">
            ¿Ya tenés cuenta?
            <a href="{{ route('buyer.login') }}" class="text-purple-700 font-semibold hover:underline">Iniciá sesión</a>
        </p>
        <p class="text-center mt-2">
            <a href="{{ route('home') }}" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">← Volver al inicio</a>
        </p>

    </div>
</div>
