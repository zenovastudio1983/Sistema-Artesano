<div class="relative" x-data="{ focused: false }">
    <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input
            wire:model.live.debounce.300ms="query"
            type="search"
            placeholder="Buscar..."
            @focus="focused = true"
            @blur="setTimeout(() => focused = false, 200)"
            class="pl-9 pr-4 py-1.5 text-sm border border-gray-200 rounded-lg w-48 focus:w-64 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50"
        />
    </div>
</div>
