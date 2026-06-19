<div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Nueva contraseña</h3>
    <p class="text-sm text-gray-500 mb-6">Elige una contraseña segura de al menos 8 caracteres.</p>

    <form wire:submit="submit" class="space-y-5">
        <div>
            <label for="email" class="form-label">Correo electrónico</label>
            <input
                wire:model="email"
                id="email"
                type="email"
                autocomplete="email"
                class="form-input @error('email') border-red-500 @enderror"
            />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="form-label">Nueva contraseña</label>
            <input
                wire:model="password"
                id="password"
                type="password"
                autocomplete="new-password"
                autofocus
                class="form-input @error('password') border-red-500 @enderror"
            />
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
            <input
                wire:model="password_confirmation"
                id="password_confirmation"
                type="password"
                autocomplete="new-password"
                class="form-input"
            />
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-2.5">
            <span wire:loading.remove>Restablecer contraseña</span>
            <span wire:loading class="flex items-center gap-2">
                <svg class="spinner w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Guardando...
            </span>
        </button>
    </form>
</div>
