<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — {{ config('erp.company.name', 'Artisan ERP') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased">

<div class="flex h-full" x-data="{ sidebarOpen: false }">
    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-gray-900/80 z-40 lg:hidden"
         style="display:none;"
         role="dialog" aria-modal="true">
    </div>

    {{-- Sidebar --}}
    @include('layouts.sidebar')

    {{-- Main content --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
        {{-- Top navigation --}}
        @include('layouts.topbar')

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto focus:outline-none">
            <div class="py-6">
                {{ $slot }}
            </div>
        </main>
    </div>
</div>

{{-- Notifications --}}
<div
    x-data="{ notifications: [] }"
    x-on:notify.window="
        let id = Date.now();
        notifications.push({ id, ...$event.detail });
        setTimeout(() => notifications = notifications.filter(n => n.id !== id), 4000);
    "
    class="fixed bottom-0 right-0 z-50 p-4 space-y-2"
    style="min-width: 320px;"
>
    <template x-for="notification in notifications" :key="notification.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-4"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-start p-4 rounded-lg shadow-lg border"
            :class="{
                'bg-white border-green-200': notification.type === 'success',
                'bg-white border-red-200': notification.type === 'error',
                'bg-white border-yellow-200': notification.type === 'warning',
                'bg-white border-blue-200': notification.type === 'info',
            }"
        >
            <div class="flex-shrink-0 mr-3">
                <template x-if="notification.type === 'success'">
                    <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </template>
                <template x-if="notification.type === 'error'">
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </template>
                <template x-if="notification.type === 'warning'">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </template>
                <template x-if="notification.type === 'info'">
                    <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900" x-text="notification.title ?? notification.message"></p>
                <template x-if="notification.title">
                    <p class="mt-0.5 text-xs text-gray-500" x-text="notification.message"></p>
                </template>
            </div>
        </div>
    </template>
</div>

@livewireScripts
<script>
    window.addEventListener('livewire:initialized', () => {
        Livewire.on('notify', (data) => {
            window.dispatchEvent(new CustomEvent('notify', { detail: data[0] ?? data }));
        });
    });
</script>
</body>
</html>
