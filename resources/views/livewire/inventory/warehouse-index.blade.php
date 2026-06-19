<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Almacenes</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $warehouses->count() }} almacenes configurados</p>
        </div>
        @can('create warehouses')
        <button wire:click="openCreate" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Almacén
        </button>
        @endcan
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Grid cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @forelse($warehouses as $warehouse)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                    </svg>
                </div>
                <div class="flex items-center space-x-1">
                    @if($warehouse->is_default)
                        <span class="badge badge-blue text-xs">Principal</span>
                    @endif
                    <span class="badge {{ $warehouse->is_active ? 'badge-green' : 'badge-gray' }} text-xs">
                        {{ $warehouse->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 mb-0.5">{{ $warehouse->name }}</h3>
            @if($warehouse->code)
                <p class="text-xs text-gray-400 font-mono mb-1">{{ $warehouse->code }}</p>
            @endif
            @if($warehouse->address)
                <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $warehouse->address }}</p>
            @else
                <div class="mb-3"></div>
            @endif
            <div class="flex items-center justify-between pt-3 border-t border-gray-50">
                <span class="text-xs text-gray-400">{{ $warehouse->inventory_count }} ítems en stock</span>
                <div class="flex items-center space-x-1">
                    @can('edit warehouses')
                    <button wire:click="openEdit({{ $warehouse->id }})"
                            class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    @endcan
                    @can('delete warehouses')
                    <button wire:click="confirmDelete({{ $warehouse->id }})"
                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 py-16 text-center">
            <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
            </svg>
            <p class="text-sm text-gray-500">No hay almacenes configurados</p>
            @can('create warehouses')
            <button wire:click="openCreate" class="mt-3 btn-primary">Crear primer almacén</button>
            @endcan
        </div>
        @endforelse
    </div>

    {{-- Form modal --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500/75" wire:click="resetForm"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                {{ $editId ? 'Editar almacén' : 'Nuevo almacén' }}
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Nombre *</label>
                    <input wire:model="name" type="text" class="form-input @error('name') border-red-500 @enderror" placeholder="Nombre del almacén">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Código</label>
                    <input wire:model="code" type="text" class="form-input" placeholder="Ej: ALM-01">
                </div>
                <div>
                    <label class="form-label">Dirección</label>
                    <input wire:model="address" type="text" class="form-input" placeholder="Dirección física (opcional)">
                </div>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Activo</span>
                    </label>
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
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Eliminar almacén</h3>
                    <p class="text-sm text-gray-500">El stock asociado quedará sin almacén.</p>
                </div>
            </div>
            <div class="flex justify-end space-x-3">
                <button wire:click="$set('showDeleteModal', false)" class="btn-secondary">Cancelar</button>
                <button wire:click="delete" wire:loading.attr="disabled" class="btn-danger">
                    <span wire:loading.remove wire:target="delete">Eliminar</span>
                    <span wire:loading wire:target="delete">Eliminando...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
