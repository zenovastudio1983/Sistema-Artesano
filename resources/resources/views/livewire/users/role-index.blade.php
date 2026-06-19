<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Roles y Permisos</h1>
            <p class="text-sm text-gray-500 mt-1">Gestiona los roles y sus permisos asociados</p>
        </div>
        @can('assign roles')
        <button wire:click="openCreate" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Rol
        </button>
        @endcan
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Roles grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        @forelse($roles as $role)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ ucfirst($role->name) }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $role->users_count }} usuario(s) asignado(s)</p>
                </div>
                <div class="flex items-center space-x-1">
                    @can('assign roles')
                    <button wire:click="openEdit({{ $role->id }})"
                            class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    @if($role->users_count == 0)
                    <button wire:click="confirmDelete({{ $role->id }})"
                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                    @endif
                    @endcan
                </div>
            </div>
            @if($role->permissions->count())
            <div class="flex flex-wrap gap-1">
                @foreach($role->permissions->take(8) as $perm)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-indigo-50 text-indigo-600">{{ $perm->name }}</span>
                @endforeach
                @if($role->permissions->count() > 8)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-gray-100 text-gray-500">+{{ $role->permissions->count() - 8 }} más</span>
                @endif
            </div>
            @else
            <p class="text-xs text-gray-400">Sin permisos asignados</p>
            @endif
        </div>
        @empty
        <div class="col-span-2 py-16 text-center">
            <p class="text-sm text-gray-500">No hay roles definidos</p>
            <button wire:click="openCreate" class="mt-3 btn-primary">Crear primer rol</button>
        </div>
        @endforelse
    </div>

    {{-- Role form modal --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500/75" wire:click="resetForm"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 z-10 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $editId ? 'Editar rol' : 'Nuevo rol' }}</h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Nombre del rol *</label>
                    <input wire:model="name" type="text" class="form-input" placeholder="Ej: vendedor">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label mb-2 block">Permisos</label>
                    <div class="space-y-4 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3">
                        @foreach($permissions as $group => $perms)
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ $group }}</p>
                            <div class="space-y-1">
                                @foreach($perms as $perm)
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" value="{{ $perm->name }}" wire:model="selectedPermissions"
                                           class="rounded border-gray-300 text-indigo-600">
                                    <span class="text-xs text-gray-700">{{ $perm->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button wire:click="resetForm" class="btn-secondary">Cancelar</button>
                <button wire:click="save" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove wire:target="save">{{ $editId ? 'Actualizar' : 'Crear' }}</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500/75" wire:click="$set('showDeleteModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Eliminar rol</h3>
            <p class="text-sm text-gray-500 mb-4">Solo se pueden eliminar roles sin usuarios asignados.</p>
            <div class="flex justify-end space-x-3">
                <button wire:click="$set('showDeleteModal', false)" class="btn-secondary">Cancelar</button>
                <button wire:click="delete" class="btn-danger">Eliminar</button>
            </div>
        </div>
    </div>
    @endif

</div>
