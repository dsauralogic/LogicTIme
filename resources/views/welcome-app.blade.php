<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Fichajes') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Estilos personalizados -->
        <style>
            .logo-custom-2 {
                height: 80px;
                width: auto;
            }
            @media (min-width: 640px) {
                .logo-custom-2 {
                    height: 120px;
                }
            }
        </style>
    </head>
    <body class="antialiased bg-gray-100 dark:bg-gray-900">
        <div class="min-h-screen flex flex-col items-center justify-center px-4">
            <!-- Logo de la empresa -->
            <div class="mb-6">
                <img src="{{ asset('images/logo.png') }}" alt="LogicACFI Service" class="logo-custom-2">
            </div>

            <!-- Título -->
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 dark:text-gray-200 mb-4 text-center">
                Bienvenido a LogicTime
            </h1>

            <!-- Subtítulo -->
            <p class="text-base sm:text-lg text-gray-600 dark:text-gray-400 mb-8 text-center">
                Gestiona tus fichajes de manera sencilla y eficiente
            </p>

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-indigo-600 text-white rounded-md font-semibold text-sm uppercase tracking-widest hover:bg-indigo-500 text-center">
                            Ir al Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-3 bg-indigo-600 text-white rounded-md font-semibold text-sm uppercase tracking-widest hover:bg-indigo-500 text-center">
                            Iniciar Sesión
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-6 py-3 bg-gray-600 text-white rounded-md font-semibold text-sm uppercase tracking-widest hover:bg-gray-500 text-center">
                                Registrarse
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </body>
</html>
