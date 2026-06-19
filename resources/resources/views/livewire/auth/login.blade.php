<div>
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Iniciar sesión</h3>

    <form wire:submit="login" class="space-y-5">
        <!-- Email -->
        <div>
            <label for="email" class="form-label">Correo electrónico</label>
            <input
                wire:model="email"
                id="email"
                type="email"
                autocomplete="email"
                autofocus
                class="form-input @error('email') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                placeholder="admin@artisanerp.local"
            />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="form-label">Contraseña</label>
            <input
                wire:model="password"
                id="password"
                type="password"
                autocomplete="current-password"
                class="form-input @error('password') border-red-500 @enderror"
            />
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember + Forgot password -->
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input wire:model="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <span class="text-sm text-gray-600">Recordarme</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium">
                ¿Olvidaste tu contraseña?
            </a>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-primary w-full justify-center py-2.5">
            <span wire:loading.remove>Ingresar al sistema</span>
            <span wire:loading class="flex items-center gap-2">
                <svg class="spinner w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Verificando...
            </span>
        </button>
    </form>

    @env('local')
    <!-- Demo credentials — only visible in local environment -->
    <div class="mt-6 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-xs font-medium text-blue-700 mb-1">Credenciales demo:</p>
        <p class="text-xs text-blue-600">admin@artisanerp.local / Admin@ERP2024!</p>
    </div>
    @endenv
</div>
