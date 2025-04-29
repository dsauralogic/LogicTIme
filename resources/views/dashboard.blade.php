<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($fichajeActivo && $fichajeActivo->latitud === null && $fichajeActivo->longitud === null)
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                            No se pudo obtener tu ubicación. Asegúrate de que la geolocalización esté habilitada en tu navegador.
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        {{ __("Estas conectado!") }}
                    </div>

                    @if ($fichajeActivo)
                        <div class="mt-4 p-4 border border-gray-300 rounded-md bg-gray-50 dark:bg-gray-700 shadow-lg">
                            <h3 class="text-lg font-semibold text-center sm:text-left text-indigo-600 dark:text-indigo-400">Fichaje Activo</h3>
                            <div class="mt-4 space-y-3 text-sm sm:text-base">
                                <div class="flex flex-col sm:flex-row sm:items-center p-3 bg-indigo-100 dark:bg-indigo-900 rounded-md">
                                    <span class="font-bold sm:w-1/3">Proyecto:</span>
                                    <span class="sm:w-2/3 font-medium text-gray-800 dark:text-gray-200">{{ $fichajeActivo->proyecto_nombre }}</span>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center p-3 bg-indigo-100 dark:bg-indigo-900 rounded-md">
                                    <span class="font-bold sm:w-1/3">Tarea:</span>
                                    <span class="sm:w-2/3 font-medium text-gray-800 dark:text-gray-200">{{ $fichajeActivo->tarea_nombre }}</span>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center p-3 bg-gray-100 dark:bg-gray-600 rounded-md">
                                    <span class="font-bold sm:w-1/3">Inicio:</span>
                                    <span class="sm:w-2/3 text-gray-700 dark:text-gray-300">{{ $fichajeActivo->inicio->format('d/m/Y H:i:s') }}</span>
                                </div>
                                @if ($fichajeActivo->estado == 'pausado')
                                    <div class="flex flex-col sm:flex-row sm:items-center p-3 bg-yellow-100 dark:bg-yellow-900 rounded-md">
                                        <span class="font-bold sm:w-1/3">Pausado desde:</span>
                                        <span class="sm:w-2/3 text-yellow-800 dark:text-yellow-300">{{ $fichajeActivo->pausa->format('d/m/Y H:i:s') }}</span>
                                    </div>
                                @endif
                                <div class="flex flex-col sm:flex-row sm:items-center p-3 bg-green-100 dark:bg-green-900 rounded-md">
                                    <span class="font-bold sm:w-1/3">Tiempo activo:</span>
                                    <span class="sm:w-2/3 text-2xl font-bold text-green-600 dark:text-green-400" id="tiempo-activo">
                                        {{ gmdate('H:i:s', $fichajeActivo->active_seconds) }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-col sm:flex-row gap-2 justify-center sm:justify-start">
                                @if ($fichajeActivo->estado == 'activo')
                                    <form action="{{ route('fichaje.pausar', $fichajeActivo->id) }}" method="POST" class="ubicacion-form w-full sm:w-auto">
                                        @csrf
                                        <input type="hidden" name="latitud" class="latitud">
                                        <input type="hidden" name="longitud" class="longitud">
                                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-yellow-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-yellow-500">
                                            Pausar Fichaje
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('fichaje.reanudar', $fichajeActivo->id) }}" method="POST" class="ubicacion-form w-full sm:w-auto">
                                        @csrf
                                        <input type="hidden" name="latitud" class="latitud">
                                        <input type="hidden" name="longitud" class="longitud">
                                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-green-500">
                                            Reanudar Fichaje
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('fichaje.finalizar', $fichajeActivo->id) }}" method="POST" class="ubicacion-form w-full sm:w-auto" data-require-geolocation="true">
                                    @csrf
                                    <input type="hidden" name="latitud" class="latitud">
                                    <input type="hidden" name="longitud" class="longitud">
                                    <input type="hidden" name="notes" class="notes">
                                    <button type="button" onclick="openNotesModal(this.form)" class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-red-500">
                                        Finalizar Fichaje
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 text-center">
                            <a href="{{ route('fichar.seleccionar') }}" class="inline-block w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-500">
                                {{ __('Iniciar Fichaje') }}
                            </a>
                        </div>
                    @endif

                    <div class="mt-4 text-center sm:text-left">
                        <a href="{{ route('fichaje.datos') }}" class="text-blue-600 hover:underline">
                            Gestionar mis datos de ubicación
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para añadir notas -->
    <div id="notesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-11/12 sm:w-96">
            <h3 class="text-lg font-semibold mb-4">Añadir Notas</h3>
            <textarea id="notesInput" class="w-full p-2 border border-gray-300 rounded-md" rows="4" placeholder="Escribe tus notas aquí...">Tiempo registrado desde la aplicación de fichajes</textarea>
            <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2">
                <button onclick="closeNotesModal()" class="w-full sm:w-auto px-4 py-2 bg-gray-500 text-white rounded-md">Cancelar</button>
                <button onclick="submitWithNotes()" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md">Confirmar</button>
            </div>
        </div>
    </div>

    <script>
        // Verificar si ya se mostró el mensaje de geolocalización
        if (!localStorage.getItem('geolocationNoticeShown')) {
            alert('Esta aplicación registra tu ubicación al iniciar y finalizar un fichaje para verificar tu lugar de trabajo, conforme a nuestro interés legítimo (Art. 6.1.f del RGPD). Consulta nuestra política de privacidad para más información y para ejercer tus derechos de acceso, rectificación o supresión.');
            localStorage.setItem('geolocationNoticeShown', 'true');
        }

        let currentForm;

        function openNotesModal(form) {
            currentForm = form;
            const modal = document.getElementById('notesModal');
            modal.classList.remove('hidden');
        }

        function closeNotesModal() {
            const modal = document.getElementById('notesModal');
            modal.classList.add('hidden');
        }

        async function submitWithNotes() {
            const notesInput = document.getElementById('notesInput').value;
            currentForm.querySelector('.notes').value = notesInput;

            const startTime = performance.now();
            const requireGeolocation = currentForm.dataset.requireGeolocation === 'true';
            const ubicacion = await obtenerUbicacion(requireGeolocation);
            const endTime = performance.now();
            console.log('Tiempo total para obtener ubicación y preparar formulario:', (endTime - startTime) / 1000, 'segundos');

            const formData = new FormData(currentForm);
            formData.set('latitud', ubicacion.latitud ?? '');
            formData.set('longitud', ubicacion.longitud ?? '');

            fetch(currentForm.action, {
                method: 'POST',
                body: formData,
                redirect: 'follow'
            }).then(response => {
                if (response.ok) {
                    window.location.href = '{{ route("dashboard") }}';
                } else {
                    console.error('Error en la respuesta del servidor:', response.status, response.statusText);
                    alert('Ocurrió un error al procesar la solicitud. Intenta de nuevo.');
                }
            }).catch(error => {
                console.error('Error al enviar el formulario:', error);
                alert('Ocurrió un error al enviar el formulario. Intenta de nuevo.');
            });

            closeNotesModal();
        }

        function obtenerUbicacion(requireGeolocation = false) {
            return new Promise((resolve, reject) => {
                console.log('Intentando obtener la ubicación...');
                const startTime = performance.now();
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const endTime = performance.now();
                            console.log('Ubicación obtenida:', position.coords.latitude, position.coords.longitude);
                            console.log('Tiempo para obtener ubicación:', (endTime - startTime) / 1000, 'segundos');
                            resolve({
                                latitud: position.coords.latitude,
                                longitud: position.coords.longitude
                            });
                        },
                        (error) => {
                            const endTime = performance.now();
                            console.error('Error al obtener la ubicación:', {
                                code: error.code,
                                message: error.message
                            });
                            console.log('Tiempo para intentar obtener ubicación:', (endTime - startTime) / 1000, 'segundos');
                            if (requireGeolocation) {
                                let errorMessage = '';
                                switch (error.code) {
                                    case error.PERMISSION_DENIED:
                                        errorMessage = 'La geolocalización está desactivada. Por favor, habilítala en la configuración del navegador para continuar.';
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        errorMessage = 'No se pudo obtener la ubicación. Asegúrate de que los servicios de ubicación estén habilitados en tu dispositivo.';
                                        break;
                                    case error.TIMEOUT:
                                        errorMessage = 'Se agotó el tiempo para obtener la ubicación. Intenta de nuevo.';
                                        break;
                                    default:
                                        errorMessage = 'Error desconocido al obtener la ubicación. Intenta de nuevo.';
                                }
                                alert(errorMessage);
                            }
                            resolve({
                                latitud: null,
                                longitud: null
                            });
                        },
                        {
                            timeout: 3000,
                            maximumAge: 10000,
                            enableHighAccuracy: false
                        }
                    );
                } else {
                    console.error('Geolocalización no soportada por el navegador');
                    if (requireGeolocation) {
                        alert('Tu navegador no soporta geolocalización. Por favor, usa un navegador compatible.');
                    }
                    resolve({
                        latitud: null,
                        longitud: null
                    });
                }
            });
        }

        @if ($fichajeActivo && $fichajeActivo->estado == 'activo')
            const tiempoActivoElement = document.getElementById('tiempo-activo');
            let activeSeconds = {{ $fichajeActivo->active_seconds }};
            const inicio = new Date('{{ $fichajeActivo->reanudado ?? $fichajeActivo->inicio }}');

            function actualizarReloj() {
                const ahora = new Date();
                const diffSeconds = Math.floor((ahora - inicio) / 1000);
                const totalSeconds = activeSeconds + diffSeconds;

                const horas = Math.floor(totalSeconds / 3600);
                const minutos = Math.floor((totalSeconds % 3600) / 60);
                const segundos = totalSeconds % 60;

                tiempoActivoElement.textContent =
                    (horas < 10 ? '0' + horas : horas) + ':' +
                    (minutos < 10 ? '0' + minutos : minutos) + ':' +
                    (segundos < 10 ? '0' + segundos : segundos);
            }

            setInterval(actualizarReloj, 1000);
            actualizarReloj();
        @endif
    </script>
</x-app-layout>
