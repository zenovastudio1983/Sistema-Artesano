<div>
    <div class="max-w-md mx-auto">

        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block">
                <h1 class="font-bold text-2xl text-gray-900" style="font-family: 'Playfair Display', serif;">ArtEmAr</h1>
            </a>
            <h2 class="text-lg font-semibold text-gray-800 mt-4">Iniciar sesión</h2>
            <p class="text-sm text-gray-500 mt-1">Accedé a tu cuenta para comprar</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <form wire:submit="login" class="space-y-4">

                <div>
                    <label class="form-label">Correo electrónico</label>
                    <input wire:model="email" type="email" class="form-input" placeholder="tu@correo.com" autocomplete="email">
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Contraseña</label>
                    <input wire:model="password" type="password" class="form-input" placeholder="••••••••" autocomplete="current-password">
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model="remember" type="checkbox" class="w-4 h-4 rounded text-purple-600">
                    <span class="text-sm text-gray-600">Mantener sesión iniciada</span>
                </label>

                <button type="submit"
                        class="w-full bg-purple-700 hover:bg-purple-800 text-white font-semibold py-3 rounded-xl transition-colors mt-2">
                    <span wire:loading.remove>Ingresar</span>
                    <span wire:loading>Verificando…</span>
                </button>

            </form>
        </div>

        <p class="text-center text-sm text-gray-500 mt-5">
            ¿No tenés cuenta?
            <a href="{{ route('buyer.register') }}" class="text-purple-700 font-semibold hover:underline">Registrate gratis</a>
        </p>
        <p class="text-center mt-2">
            <a href="{{ route('home') }}" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">← Volver al inicio</a>
        </p>

    </div>
</div>
