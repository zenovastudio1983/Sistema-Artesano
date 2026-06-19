<header class="bg-white border-b border-gray-200 flex-shrink-0">
    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
        {{-- Mobile menu button --}}
        <button @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Page title / breadcrumb --}}
        <div class="flex items-center space-x-4">
            @isset($pageTitle)
                <h1 class="text-lg font-semibold text-gray-900">{{ $pageTitle }}</h1>
            @endisset
        </div>

        {{-- Right side --}}
        <div class="flex items-center space-x-4">
            {{-- Stock alerts badge --}}
            @livewire('dashboard.stock-alerts-badge')

            {{-- Quick search --}}
            <div class="hidden md:block">
                <livewire:dashboard.quick-search />
            </div>

            {{-- User menu --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="flex items-center space-x-2 text-sm focus:outline-none bg-gray-100 hover:bg-gray-200 rounded-full pl-1 pr-3 py-1 transition-colors">
                    <img src="{{ auth()->user()->avatar_url }}"
                         alt="{{ auth()->user()->name }}"
                         class="w-7 h-7 rounded-full">
                    <span class="hidden md:block font-medium text-gray-700">{{ auth()->user()->name }}</span>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open"
                     @click.away="open = false"
                     x-transition
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-xs text-gray-500">Sesión activa</p>
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="{{ route('settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        Configuración
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
