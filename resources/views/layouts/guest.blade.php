<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-white">
    <div class="min-h-screen flex">

        {{-- Left brand panel (desktop only) --}}
        <div class="hidden lg:flex lg:flex-col lg:w-2/5 xl:w-1/2 bg-gradient-to-br from-gray-950 via-indigo-950 to-gray-900 px-12 py-16 relative overflow-hidden flex-shrink-0">

            {{-- Decorative blobs --}}
            <div class="absolute -top-32 -right-32 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 -left-16 w-72 h-72 bg-indigo-600/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute top-1/2 right-0 w-48 h-48 bg-purple-500/5 rounded-full blur-2xl pointer-events-none"></div>

            {{-- Logo --}}
            <div class="flex items-center space-x-3 relative z-10">
                <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-lg">{{ config('app.name', 'Artisan ERP') }}</span>
            </div>

            {{-- Central content --}}
            <div class="flex-1 flex flex-col justify-center relative z-10 mt-16 lg:mt-0">
                <h1 class="text-4xl font-bold text-white leading-tight">
                    Gestión completa<br>para tu taller artesanal
                </h1>
                <p class="text-indigo-200 mt-4 text-base leading-relaxed">
                    Controla inventario, producción, compras y ventas desde un solo lugar. Diseñado para productores artesanales.
                </p>

                <ul class="mt-10 space-y-4">
                    @foreach([
                        ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'text' => 'Inventario en tiempo real'],
                        ['icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'text' => 'Órdenes de producción con recetas'],
                        ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'text' => 'Reportes de rentabilidad y margen'],
                    ] as $feature)
                    <li class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-indigo-500/20 rounded-md flex items-center justify-center mt-0.5">
                            <svg class="w-3.5 h-3.5 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-indigo-100 text-sm">{{ $feature['text'] }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Footer --}}
            <p class="text-xs text-indigo-500 relative z-10">© {{ date('Y') }} Artisan ERP · Sistema de gestión artesanal</p>
        </div>

        {{-- Right form panel --}}
        <div class="flex-1 flex flex-col justify-center px-4 sm:px-8 lg:px-16 py-12 bg-white">

            {{-- Mobile logo (hidden on desktop where left panel is visible) --}}
            <div class="lg:hidden flex flex-col items-center mb-10">
                <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <h2 class="mt-4 text-xl font-bold text-gray-900">{{ config('app.name', 'Artisan ERP') }}</h2>
                <p class="mt-1 text-sm text-gray-500">Sistema de gestión artesanal</p>
            </div>

            {{-- Form wrapper --}}
            <div class="w-full max-w-sm mx-auto lg:mx-0">
                {{-- Card on mobile, flat on desktop --}}
                <div class="bg-white py-8 px-6 rounded-2xl border border-gray-100 shadow-sm sm:px-8 lg:shadow-none lg:border-0 lg:p-0">
                    {{ $slot }}
                </div>
            </div>
        </div>

    </div>

    @livewireScripts
</body>
</html>
