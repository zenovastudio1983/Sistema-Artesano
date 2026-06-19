<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Categorías</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $categories->count() }} categorías</p>
        </div>
        @can('create categories')
        <button wire:click="openCreate" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva Categoría
        </button>
        @endcan
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Search --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar categorías..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
    </div>

    {{-- Categories --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @forelse($categories->whereNull('parent_id') as $root)
        <div class="border-b border-gray-50 last:border-0">
            <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $root->color ?? '#6366f1' }}"></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $root->name }}</p>
                        @if($root->description)
                            <p class="text-xs text-gray-400">{{ $root->description }}</p>
                        @endif
                    </div>
                    <span class="badge badge-gray text-xs">{{ $root->products_count }} productos</span>
                    @if(!$root->is_active)
                        <span class="badge bg-yellow-100 text-yellow-700">Inactiva</span>
                    @endif
                </div>
                <div class="flex items-center space-x-1">
                    @can('edit categories')
                    <button wire:click="openEdit({{ $root->id }})"
                            class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    @endcan
                    @can('delete categories')
                    <button wire:click="confirmDelete({{ $root->id }})"
                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                    @endcan
                </div>
            </div>
            @foreach($root->children as $child)
            <div class="flex items-center justify-between px-6 py-3 bg-gray-50/30 border-t border-gray-50 hover:bg-gray-50 transition-colors">
                <div class="flex items-center space-x-3 pl-6">
                    <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $child->color ?? '#a5b4fc' }}"></div>
                    <p class="text-sm text-gray-700">{{ $child->name }}</p>
                    <span class="badge badge-gray text-xs">{{ $child->products_count }} productos</span>
                </div>
                <div class="flex items-center space-x-1">
                    @can('edit categories')
                    <button wire:click="openEdit({{ $child->id }})"
                            class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    @endcan
                    @can('delete categories')
                    <button wire:click="confirmDelete({{ $child->id }})"
                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                    @endcan
                </div>
            </div>
            @endforeach
        </div>
        @empty
        <div class="px-6 py-16 text-center">
            <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <p class="text-sm text-gray-500">No hay categorías registradas</p>
            @can('create categories')
            <button wire:click="openCreate" class="mt-3 btn-primary btn-sm">Crear primera categoría</button>
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
                {{ $editId ? 'Editar categoría' : 'Nueva categoría' }}
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Nombre *</label>
                    <input wire:model="name" type="text" class="form-input @error('name') border-red-500 @enderror" placeholder="Nombre de la categoría">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Categoría padre</label>
                    <select wire:model="parent_id" class="form-input">
                        <option value="">Sin padre (raíz)</option>
                        @foreach($parentOptions as $opt)
                            <option value="{{ $opt->id }}" @if($editId == $opt->id) disabled @endif>{{ $opt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Descripción</label>
                    <input wire:model="description" type="text" class="form-input" placeholder="Opcional">
                </div>
                <div>
                    <label class="form-label">Color</label>
                    <input wire:model="color" type="color" class="h-10 w-full rounded-lg border border-gray-300 p-1 cursor-pointer">
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
                    <h3 class="text-lg font-semibold text-gray-900">Eliminar categoría</h3>
                    <p class="text-sm text-gray-500">Los productos asociados quedarán sin categoría.</p>
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
