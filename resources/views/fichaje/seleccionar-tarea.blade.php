<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Seleccionar Tarea
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3>Proyectos disponibles para {{ $trabajador }}</h3>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (count($proyectos) > 0)
                        <form id="seleccionar-tarea-form" action="{{ route('fichaje.iniciar') }}" method="POST">
                            @csrf
                            <input type="hidden" name="latitud" id="latitud">
                            <input type="hidden" name="longitud" id="longitud">
                            <div class="mt-4">
                                <label for="proyecto" class="block text-sm font-medium text-gray-700">Proyecto:</label>
                                <select id="proyecto" name="proyecto" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Selecciona un proyecto</option>
                                    @foreach ($proyectos as $proyecto)
                                        <option value="{{ $proyecto['id'] }}">{{ $proyecto['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-4">
                                <label for="tarea" class="block text-sm font-medium text-gray-700">Tarea:</label>
                                <select id="tarea" name="tarea" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required disabled>
                                    <option value="">Selecciona un proyecto primero</option>
                                </select>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                    Iniciar Fichaje
                                </button>
                            </div>
                        </form>
                    @else
                        <p>No hay proyectos disponibles.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Obtener la ubicación al cargar la página
        function obtenerUbicacion() {
            return new Promise((resolve, reject) => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            document.getElementById('latitud').value = position.coords.latitude;
                            document.getElementById('longitud').value = position.coords.longitude;
                            resolve();
                        },
                        (error) => {
                            console.error('Error al obtener la ubicación:', error);
                            reject(error);
                        }
                    );
                } else {
                    console.error('Geolocalización no soportada por el navegador');
                    reject(new Error('Geolocalización no soportada'));
                }
            });
        }

        // Obtener la ubicación cuando se carga la página
        window.addEventListener('load', obtenerUbicacion);

        document.getElementById('proyecto').addEventListener('change', function () {
            const projectId = this.value;
            const tareaSelect = document.getElementById('tarea');

            if (!projectId) {
                tareaSelect.innerHTML = '<option value="">Selecciona un proyecto primero</option>';
                tareaSelect.disabled = true;
                return;
            }

            tareaSelect.disabled = false;
            tareaSelect.innerHTML = '<option value="">Cargando tareas...</option>';

            fetch(`/fichaje/tareas?project_id=${projectId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    tareaSelect.innerHTML = '<option value="">Selecciona una tarea</option>';
                    if (data.length > 0) {
                        data.forEach(tarea => {
                            const option = document.createElement('option');
                            option.value = tarea.task_id;
                            option.textContent = tarea.nombre;
                            tareaSelect.appendChild(option);
                        });
                    } else {
                        tareaSelect.innerHTML = '<option value="">No hay tareas disponibles</option>';
                    }
                })
                .catch(error => {
                    tareaSelect.innerHTML = '<option value="">Error al cargar tareas</option>';
                    console.error('Error al cargar tareas:', error);
                });
        });
    </script>
</x-app-layout>
