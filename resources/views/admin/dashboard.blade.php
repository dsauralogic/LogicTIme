<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard de Administración') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                    <!-- Estadísticas -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-lg">
                            <h3 class="text-lg font-semibold">Total Fichajes</h3>
                            <p class="text-2xl font-bold">{{ $totalFichajes }}</p>
                        </div>
                        <div class="p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                            <h3 class="text-lg font-semibold">Tiempo Total Fichado</h3>
                            <p class="text-2xl font-bold">{{ gmdate('H:i:s', $totalTiempoFichado) }}</p>
                        </div>
                        <div class="p-4 bg-purple-100 dark:bg-purple-900 rounded-lg">
                            <h3 class="text-lg font-semibold">Proyectos Activos</h3>
                            <p class="text-2xl font-bold">{{ $proyectosActivos->count() }}</p>
                        </div>
                    </div>

                    <!-- Gestión de usuarios -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-4">Gestión de Usuarios</h3>
                        <div class="mb-4">
                            <a href="{{ route('admin.users.create') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-500">
                                Crear Nuevo Usuario
                            </a>
                        </div>
                        <table class="min-w-full border border-gray-300">
                            <thead>
                                <tr class="bg-gray-200 dark:bg-gray-700">
                                    <th class="px-4 py-2 border">Nombre</th>
                                    <th class="px-4 py-2 border">Email</th>
                                    <th class="px-4 py-2 border">Es Admin</th>
                                    <th class="px-4 py-2 border">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td class="px-4 py-2 border">{{ $user->name }}</td>
                                        <td class="px-4 py-2 border">{{ $user->email }}</td>
                                        <td class="px-4 py-2 border">{{ $user->is_admin ? 'Sí' : 'No' }}</td>
                                        <td class="px-4 py-2 border">
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-600 hover:underline">Editar</a>
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('¿Estás seguro de que quieres eliminar este usuario?')">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
