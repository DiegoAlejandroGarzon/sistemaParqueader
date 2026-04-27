<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Parqueadero</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .bg-parking {
            background: linear-gradient(135deg, #004c99 0%, #00aeef 100%);
        }
    </style>
</head>

<body class="antialiased">
    <div
        class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-gray-100 dark:bg-gray-900 selection:bg-blue-500 selection:text-white">
        @if (Route::has('login'))
            <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-blue-500">Dashboard</a>
                @else
                    <a href="{{ route('login') }}"
                        class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-blue-500">Iniciar
                        Sesión</a>
                @endauth
            </div>
        @endif

        <div class="max-w-7xl mx-auto p-6 lg:p-8 text-center text-gray-900 dark:text-gray-100">
            <div class="flex justify-center mb-8">
                <img src="{{ asset('logo.png') }}" alt="Parking Logo" class="h-48 w-auto">
            </div>

            <h1
                class="text-5xl font-extrabold mb-4 text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">
                SISTEMA DE PARQUEADERO
            </h1>

            <p class="text-xl text-gray-500 dark:text-gray-400 mb-12 max-w-2xl mx-auto">
                Gestión eficiente de entradas, salidos y facturación con tecnología térmica.
                Simple, rápido y profesional.
            </p>

            <div class="flex justify-center gap-4">
                <a href="{{ route('login') }}"
                    class="px-8 py-4 bg-parking text-white font-bold rounded-xl shadow-lg hover:shadow-2xl transition-all transform hover:-translate-y-1">
                    Acceder al Sistema
                </a>
            </div>

            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm">
                    <div class="text-3xl mb-2">⚡</div>
                    <h3 class="font-bold text-lg mb-2">Entrada Rápida</h3>
                    <p class="text-sm text-gray-400">Registra vehículos en segundos solo con la placa.</p>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm">
                    <div class="text-3xl mb-2">🖨️</div>
                    <h3 class="font-bold text-lg mb-2">Impresión Térmica</h3>
                    <p class="text-sm text-gray-400">Recibos optimizados para tiqueteras de 58/80mm.</p>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm">
                    <div class="text-3xl mb-2">📊</div>
                    <h3 class="font-bold text-lg mb-2">Estadísticas</h3>
                    <p class="text-sm text-gray-400">Reportes de recaudos y picos de entrada en tiempo real.</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
