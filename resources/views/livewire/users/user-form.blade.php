<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <a href="{{ route('users.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Usuarios</a>
                <span class="text-gray-300">/</span>
                <span class="text-sm text-gray-700">{{ $userId ? 'Editar' : 'Nuevo' }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $userId ? 'Editar usuario' : 'Nuevo usuario' }}</h1>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <div>
                <label class="form-label">Nombre completo *</label>
                <input wire:model="name" type="text" class="form-input @error('name') border-red-500 @enderror">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Email *</label>
                <input wire:model="email" type="email" class="form-input @error('email') border-red-500 @enderror">
                @error('email')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Contraseña {{ $userId ? '(dejar vacío para mantener)' : '*' }}</label>
                <input wire:model="password" type="password" class="form-input @error('password') border-red-500 @enderror"
                       autocomplete="new-password">
                @error('password')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            @if($password)
            <div>
                <label class="form-label">Confirmar contraseña</label>
                <input wire:model="passwordConfirmation" type="password" class="form-input" autocomplete="new-password">
            </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Roles</h2>
            <div class="space-y-2">
                @foreach($roles as $role)
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" value="{{ $role->name }}" wire:model="selectedRoles"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">{{ ucfirst($role->name) }}</span>
                </label>
                @endforeach
                @if($roles->isEmpty())
                <p class="text-sm text-gray-400">No hay roles definidos. Crea roles en la sección de Roles y Permisos.</p>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <label class="flex items-center space-x-3 cursor-pointer">
                <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600">
                <span class="text-sm font-medium text-gray-700">Usuario activo</span>
            </label>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('users.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                <span wire:loading.remove>{{ $userId ? 'Actualizar' : 'Crear usuario' }}</span>
                <span wire:loading>Guardando...</span>
            </button>
        </div>

    </form>

</div>
