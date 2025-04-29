<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Historial de Fichajes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($historialFichajes->isEmpty())
                        <p>No tienes fichajes finalizados.</p>
                    @else
                        <table class="min-w-full mt-4 border border-gray-300">
                            <thead>
                                <tr class="bg-gray-200 dark:bg-gray-700">
                                    <th class="px-4 py-2 border">Proyecto</th>
                                    <th class="px-4 py-2 border">Tarea</th>
                                    <th class="px-4 py-2 border">Inicio</th>
                                    <th class="px-4 py-2 border">Fin</th>
                                    <th class="px-4 py-2 border">Tiempo Activo</th>
                                    <th class="px-4 py-2 border">Ubicación</th>
                                    <th class="px-4 py-2 border">Estado</th>
                                    <th class="px-4 py-2 border">Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($historialFichajes as $fichaje)
                                    <tr>
                                        <td class="px-4 py-2 border">{{ $fichaje->proyecto_nombre }}</td>
                                        <td class="px-4 py-2 border">{{ $fichaje->tarea_nombre }}</td>
                                        <td class="px-4 py-2 border">{{ $fichaje->inicio->format('d/m/Y H:i:s') }}</td>
                                        <td class="px-4 py-2 border">{{ $fichaje->fin->format('d/m/Y H:i:s') }}</td>
                                        <td class="px-4 py-2 border">{{ gmdate('H:i:s', $fichaje->active_seconds) }}</td>
                                        <td class="px-4 py-2 border">
                                            @if ($fichaje->latitud && $fichaje->longitud)
                                                Lat: {{ $fichaje->latitud }}, Lon: {{ $fichaje->longitud }}
                                            @else
                                                No disponible
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 border">{{ ucfirst($fichaje->estado) }}</td>
                                        <td class="px-4 py-2 border">{{ $fichaje->notes ?? 'Sin notas' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
