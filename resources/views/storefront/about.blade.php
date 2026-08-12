@extends('layouts.storefront')

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Encabezado --}}
    <div class="mb-10 text-center">
        <h1 class="text-4xl font-bold text-gray-900 mb-3" style="font-family: 'Playfair Display', serif;">
            Â¿QuiÃ©nes somos?
        </h1>
        <div class="w-12 h-1 bg-purple-600 rounded mx-auto"></div>
    </div>

    {{-- Hero imagen --}}
    <div class="rounded-3xl overflow-hidden h-64 mb-10 relative">
        <div class="absolute inset-0 bg-gradient-to-r from-purple-900/60 to-purple-700/30 z-10"></div>
        <img src="/images/hero-artemar.png"
             alt="ArtEmAr â€” productos artesanales"
             class="w-full h-full object-cover object-center">
        <div class="absolute inset-0 z-20 flex items-center justify-center">
            <p class="font-bold text-white text-2xl italic" style="font-family: 'Playfair Display', serif;">
                Hecho a mano, con amor
            </p>
        </div>
    </div>

    {{-- Contenido --}}
    <div class="prose prose-gray max-w-none space-y-8">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-3" style="font-family: 'Playfair Display', serif;">Nuestra historia</h2>
            <p class="text-gray-600 leading-relaxed">
                ArtEmAr naciÃ³ del amor por las cosas bien hechas. Comenzamos elaborando jabones y velas en casa, siguiendo recetas que respetan la piel y el ambiente. Hoy, cada producto que llega a tus manos lleva consigo el cuidado y la dedicaciÃ³n de un proceso 100% artesanal.
            </p>
        </div>

        {{-- Valores --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach([
                ['icon' => 'ðŸŒ¿', 'title' => 'Naturaleza', 'text' => 'Ingredientes naturales, sin quÃ­micos agresivos. Respetamos la piel y el planeta.'],
                ['icon' => 'âœ¨', 'title' => 'Bienestar', 'text' => 'Cada producto estÃ¡ pensado para crear momentos de calma y armonÃ­a en tu dÃ­a a dÃ­a.'],
                ['icon' => 'ðŸŽ¨', 'title' => 'Arte', 'text' => 'El proceso artesanal es parte de nuestra identidad. Cada pieza es Ãºnica e irrepetible.'],
            ] as $v)
            <div class="bg-purple-50 rounded-2xl p-6 text-center border border-purple-100">
                <span class="text-4xl">{{ $v['icon'] }}</span>
                <h3 class="font-bold text-purple-900 mt-3 mb-2" style="font-family: 'Playfair Display', serif;">{{ $v['title'] }}</h3>
                <p class="text-sm text-purple-700 leading-relaxed">{{ $v['text'] }}</p>
            </div>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-3" style="font-family: 'Playfair Display', serif;">Lo que hacemos</h2>
            <ul class="space-y-2 text-gray-600">
                <li class="flex items-start gap-2"><span class="text-purple-500 mt-0.5">âœ¦</span> Jabones artesanales con aceites esenciales y plantas medicinales</li>
                <li class="flex items-start gap-2"><span class="text-purple-500 mt-0.5">âœ¦</span> Velas de soja y cera de abejas con aromas naturales</li>
                <li class="flex items-start gap-2"><span class="text-purple-500 mt-0.5">âœ¦</span> Productos de cuidado personal libres de tÃ³xicos</li>
                <li class="flex items-start gap-2"><span class="text-purple-500 mt-0.5">âœ¦</span> Creaciones a pedido para regalos y eventos especiales</li>
            </ul>
        </div>

    </div>

    <div class="mt-8 text-center">
        <a href="{{ route('tienda.index') }}"
           class="inline-flex items-center gap-2 bg-purple-700 hover:bg-purple-800 text-white font-semibold px-6 py-3 rounded-xl transition-colors">
            Ver el catÃ¡logo â†’
        </a>
    </div>

</div>
@endsection

