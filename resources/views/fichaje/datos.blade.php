<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gestionar mis datos de ubicación
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <p>Esta aplicación registra tu ubicación al iniciar y finalizar un fichaje para verificar tu lugar de trabajo, conforme a nuestro interés legítimo (Art. 6.1.f del RGPD). Puedes ver y eliminar tus datos de ubicación a continuación.</p>

                    @if ($fichajes->isEmpty())
                        <p>No tienes fichajes registrados.</p>
                    @else
                        <table class="min-w-full mt-4 border border-gray-300">
                            <thead>
                                <tr class="bg-gray-200 dark:bg-gray-700">
                                    <th class="px-4 py-2 border">Proyecto</th>
                                    <th class="px-4 py-2 border">Tarea</th>
                                    <th class="px-4 py-2 border">Inicio</th>
                                    <th class="px-4 py-2 border">Fin</th>
                                    <th class="px-4 py-2 border">Ubicación</th>
                                    <th class="px-4 py-2 border">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fichajes as $fichaje)
                                    <tr>
                                        <td class="px-4 py-2 border">{{ $fichaje->proyecto_nombre }}</td>
                                        <td class="px-4 py-2 border">{{ $fichaje->tarea_nombre }}</td>
                                        <td class="px-4 py-2 border">{{ $fichaje->inicio->format('d/m/Y H:i:s') }}</td>
                                        <td class="px-4 py-2 border">{{ $fichaje->fin ? $fichaje->fin->format('d/m/Y H:i:s') : 'Activo' }}</td>
                                        <td class="px-4 py-2 border">
                                            @if ($fichaje->latitud && $fichaje->longitud)
                                                Lat: {{ $fichaje->latitud }}, Lon: {{ $fichaje->longitud }}
                                            @else
                                                No disponible
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 border">
                                            @if ($fichaje->latitud || $fichaje->longitud)
                                                <form action="{{ route('fichaje.eliminar-ubicacion', $fichaje->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar los datos de ubicación de este fichaje?');">
                                                    @csrf
                                                    <button type="submit" class="text-red-600 hover:underline">
                                                        Eliminar ubicación
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline">
                            Volver al dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
