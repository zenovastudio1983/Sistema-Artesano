<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtEmAr — Artesanía que transforma tu mundo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .font-playfair { font-family: 'Playfair Display', Georgia, serif; }
        .font-inter    { font-family: 'Inter', sans-serif; }

        .hero-bg {
            background-image:
                linear-gradient(to right,
                    rgba(72, 18, 110, 0.93) 0%,
                    rgba(88, 28, 135, 0.88) 40%,
                    rgba(88, 28, 135, 0.45) 65%,
                    rgba(88, 28, 135, 0.10) 100%
                ),
                url('/images/hero-artemar.png');
            background-size: cover;
            background-position: center 30%;
        }

        .btn-primary-purple {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            border: 2px solid rgba(255,255,255,0.3);
            transition: all .2s ease;
        }
        .btn-primary-purple:hover {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(109,40,217,.45);
        }

        .btn-outline-white {
            background: rgba(255,255,255,0.08);
            border: 1.5px solid rgba(255,255,255,0.35);
            backdrop-filter: blur(4px);
            transition: all .2s ease;
        }
        .btn-outline-white:hover {
            background: rgba(255,255,255,0.18);
            border-color: rgba(255,255,255,0.65);
            transform: translateY(-1px);
        }

        @media (max-width: 640px) {
            .hero-bg {
                background-image:
                    linear-gradient(to bottom,
                        rgba(72, 18, 110, 0.95) 0%,
                        rgba(88, 28, 135, 0.90) 60%,
                        rgba(88, 28, 135, 0.75) 100%
                    ),
                    url('/images/hero-artemar.png');
                background-position: center center;
            }
        }
    </style>
</head>
<body class="h-full font-inter">

<div class="hero-bg min-h-screen flex items-center">
    <div class="w-full max-w-7xl mx-auto px-6 sm:px-10 lg:px-16 py-12">
        <div class="max-w-lg">

            {{-- Logo / Marca --}}
            <div class="mb-2">
                <span class="text-purple-300 text-xs font-medium tracking-[0.3em] uppercase">Tienda artesanal</span>
            </div>
            <h1 class="font-playfair text-6xl sm:text-7xl font-bold text-white leading-none tracking-tight">
                ArtEmAr
            </h1>
            <p class="font-playfair italic text-purple-200 text-xl sm:text-2xl mt-2 leading-snug">
                Artesanía que transforma tu mundo
            </p>

            <div class="w-12 h-px bg-purple-400 my-6"></div>

            <p class="text-purple-100 text-sm sm:text-base leading-relaxed max-w-sm">
                Velas, jabones y creaciones artesanales hechas con amor, para llenar tus días de calma, belleza y armonía.
            </p>

            {{-- Botones --}}
            <div class="mt-8 flex flex-col gap-3 max-w-xs">

                {{-- Catálogo — primario --}}
                <a href="{{ route('tienda.index') }}"
                   class="btn-primary-purple flex items-center justify-between text-white font-semibold text-sm px-5 py-3.5 rounded-2xl">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h3m3 0h3a2 2 0 110 4m0 0v3m0-3h.01"/>
                        </svg>
                        CATÁLOGO
                    </span>
                    <svg class="w-4 h-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                {{-- Iniciar sesión + Regístrate --}}
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('buyer.login') }}"
                       class="btn-outline-white flex items-center justify-center gap-2 text-white text-sm font-medium px-4 py-3 rounded-2xl">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        INICIAR SESIÓN
                    </a>
                    <a href="{{ route('buyer.register') }}"
                       class="btn-outline-white flex items-center justify-center gap-2 text-white text-sm font-medium px-4 py-3 rounded-2xl">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        REGÍSTRATE
                    </a>
                </div>

                {{-- Quiénes somos + Contacto --}}
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('about') }}"
                       class="btn-outline-white flex items-center justify-center gap-2 text-white text-sm font-medium px-4 py-3 rounded-2xl">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        ¿QUIÉNES SOMOS?
                    </a>
                    <a href="{{ route('contact') }}"
                       class="btn-outline-white flex items-center justify-center gap-2 text-white text-sm font-medium px-4 py-3 rounded-2xl">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        CONTACTO
                    </a>
                </div>
            </div>

            {{-- Pie --}}
            <div class="mt-10 flex items-center gap-3 text-purple-300 text-xs tracking-widest">
                <span class="font-playfair italic">Naturaleza</span>
                <span>•</span>
                <span class="font-playfair italic">Bienestar</span>
                <span>•</span>
                <span class="font-playfair italic">Arte</span>
            </div>

        </div>
    </div>
</div>

</body>
</html>
