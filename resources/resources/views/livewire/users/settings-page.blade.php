<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Configuración</h1>
        <p class="text-sm text-gray-500 mt-1">Parámetros generales del sistema</p>
    </div>

    <div class="space-y-6">

        {{-- General --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Configuración general</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Nombre de la empresa</label>
                    <input wire:model="companyName" type="text" class="form-input" readonly
                           value="{{ config('erp.company_name', 'Artisan ERP') }}">
                    <p class="text-xs text-gray-400 mt-1">Configurable en el archivo .env (APP_NAME)</p>
                </div>
                <div>
                    <label class="form-label">Zona horaria</label>
                    <select wire:model="timezone" class="form-input" disabled>
                        @foreach($timezones as $value => $label)
                            <option value="{{ $value }}" @selected($timezone === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Configurable en config/app.php</p>
                </div>
                <div>
                    <label class="form-label">Formato de fecha</label>
                    <input type="text" class="form-input" value="{{ config('erp.date_format', 'd/m/Y') }}" readonly>
                </div>
            </div>
        </div>

        {{-- Currency --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Moneda</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Símbolo</label>
                    <input type="text" class="form-input font-mono" value="{{ config('erp.currency_symbol', 'S/') }}" readonly>
                </div>
                <div>
                    <label class="form-label">Código</label>
                    <input type="text" class="form-input font-mono" value="{{ config('erp.currency_code', 'PEN') }}" readonly>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-3">Configurable en config/erp.php o .env</p>
        </div>

        {{-- System info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Información del sistema</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Versión PHP</span>
                    <span class="font-mono text-gray-700">{{ PHP_VERSION }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Framework</span>
                    <span class="font-mono text-gray-700">Laravel {{ app()->version() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Entorno</span>
                    <span class="font-mono {{ app()->isProduction() ? 'text-green-600' : 'text-yellow-600' }}">{{ app()->environment() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Debug mode</span>
                    <span class="font-mono {{ config('app.debug') ? 'text-red-500' : 'text-green-600' }}">{{ config('app.debug') ? 'Activado' : 'Desactivado' }}</span>
                </div>
            </div>
        </div>

        {{-- Cache --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Caché</h2>
            <p class="text-sm text-gray-500 mb-3">El sistema cachea los KPIs del dashboard durante 5 minutos para mejorar el rendimiento.</p>
            <button class="btn-secondary text-sm" disabled>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Limpiar caché (usar artisan cache:clear)
            </button>
        </div>

    </div>

</div>
