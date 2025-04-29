<?php

namespace App\Http\Controllers;

use App\Models\Fichaje;
use App\Services\ZohoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FichajeController extends Controller
{
    protected $zohoService;
    protected $portalName;

    public function __construct(ZohoService $zohoService)
    {
        $this->middleware('auth');
        $this->zohoService = $zohoService;
        $this->portalName = config('zoho.portal_name', 'logicacfi');
    }

    public function seleccionarTarea(Request $request)
    {
        \Log::info('Prueba de log: Entrando en seleccionarTarea');

        \Log::info('Obteniendo usuario autenticado');
        $user = $request->user();
        \Log::info('Usuario obtenido', ['user_id' => $user->id]);

        \Log::info('Calculando trabajador');
        $trabajador = strtolower(trim(explode('@', $user->email)[0]));
        \Log::info('Trabajador calculado', ['trabajador' => $trabajador]);

        \Log::info('Generando cache key');
        $cacheKey = 'zoho_proyectos_' . $user->id;
        \Log::info('Cache key generado', ['cacheKey' => $cacheKey]);

        \Log::info('Intentando obtener proyectos de Zoho');
        try {
            $proyectos = Cache::remember($cacheKey, now()->addMinutes(10), function () {
                \Log::info('Dentro de Cache::remember, consultando Zoho');
                $endpoint = "/portal/{$this->portalName}/projects/";
                \Log::info('Endpoint generado', ['endpoint' => $endpoint]);

                $response = $this->zohoService->get($endpoint);
                \Log::info('Respuesta de Zoho obtenida', ['response' => $response]);

                $projects = $response['projects'] ?? [];
                if (empty($projects)) {
                    Log::warning('No se encontraron proyectos en Zoho');
                }

                return $projects;
            });
        } catch (\Exception $e) {
            Log::error('Error al obtener proyectos de Zoho', ['message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error al cargar los proyectos: ' . $e->getMessage());
        }

        \Log::info('Proyectos obtenidos', ['project_count' => count($proyectos)]);

        $proyectosConTareas = [];
        foreach ($proyectos as $proyecto) {
            $proyectosConTareas[] = [
                'id' => $proyecto['id_string'] ?? '',
                'name' => $proyecto['name'] ?? 'Sin nombre',
            ];
        }

        \Log::info('Proyectos procesados', ['proyectosConTareas' => $proyectosConTareas]);

        \Log::info('Devolviendo vista seleccionar-tarea');
        return view('fichaje.seleccionar-tarea', [
            'proyectos' => $proyectosConTareas,
            'trabajador' => $trabajador,
        ]);
    }

    public function fetchTasksFromZoho($access_token, $project_id)
    {
        \Log::info('Iniciando fetchTasksFromZoho', ['project_id' => $project_id]);
        $tasks = $this->zohoService->fetchTasks($project_id);

        $attempts = 0;
        $maxAttempts = 3;

        while ((isset($tasks['error']) && ($tasks['error']['code'] === 400 || $tasks['error']['code'] === 429)) && $attempts < $maxAttempts) {
            $attempts++;
            $retryAfterSeconds = 60;

            if (isset($tasks['error']['details']['message']) && preg_match('/Try again after (\d+) minutes/', $tasks['error']['details']['message'], $matches)) {
                $retryAfterSeconds = (int)$matches[1] * 60;
            }

            \Log::warning('Rate limiting detectado', [
                'project_id' => $project_id,
                'retry_after_seconds' => $retryAfterSeconds,
                'attempt' => $attempts,
            ]);

            if ($attempts === $maxAttempts) {
                throw new \Exception('Rate limiting exceeded. Please try again later.');
            }

            sleep(min($retryAfterSeconds, pow(2, $attempts)));

            $tasks = $this->zohoService->fetchTasks($project_id);
        }

        \Log::info('fetchTasksFromZoho completado', ['task_count' => count($tasks)]);
        return $tasks;
    }

    public function obtenerTareas(Request $request)
    {
        \Log::info('Entrando en obtenerTareas', ['project_id' => $request->query('project_id')]);

        $user = Auth::user();
        \Log::info('Usuario autenticado', ['user_id' => $user->id]);

        $trabajadorZoho = strtoupper(explode('@', $user->email)[0]);
        \Log::info('Trabajador Zoho', ['trabajador' => $trabajadorZoho]);

        $project_id = $request->query('project_id');
        \Log::info('Project ID recibido', ['project_id' => $project_id]);

        if (!$project_id) {
            \Log::warning('Falta project_id en obtenerTareas');
            return response()->json(['error' => 'Falta el project_id'], 400);
        }

        try {
            \Log::info('Obteniendo access token de Zoho');
            $access_token = $this->zohoService->getAccessToken();
            \Log::info('Access token obtenido', ['token' => substr($access_token, 0, 10) . '...']);

            \Log::info('Llamando a fetchTasksFromZoho');
            $tasks = $this->fetchTasksFromZoho($access_token, $project_id);
            \Log::info('Tareas obtenidas', ['task_count' => count($tasks)]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener tareas de Zoho', ['message' => $e->getMessage(), 'project_id' => $project_id]);
            return response()->json(['error' => 'Error al obtener tareas: ' . $e->getMessage()], 500);
        }

        $resultados = [];
        foreach ($tasks as $task) {
            $trabajador = collect($task['custom_fields'] ?? [])
                ->firstWhere('label_name', 'Trabajador')['value'] ?? null;

            if ($trabajador && strtoupper($trabajador) === $trabajadorZoho) {
                $resultados[] = [
                    'project_id' => $project_id,
                    'project_name' => 'Proyecto Lógico',
                    'task_id' => $task['id_string'],
                    'nombre' => $task['name'],
                ];
            }
        }

        \Log::info('Enviando resultados', ['result_count' => count($resultados)]);
        return response()->json($resultados);
    }

    private function obtenerUbicacion(Request $request)
    {
        $latitud = $request->input('latitud');
        $longitud = $request->input('longitud');

        if (is_null($latitud) || is_null($longitud)) {
            \Log::warning('No se pudo obtener la ubicación', ['user_id' => Auth::id()]);
            return ['latitud' => null, 'longitud' => null];
        }

        return [
            'latitud' => (float) $latitud,
            'longitud' => (float) $longitud,
        ];
    }

    public function iniciarFichaje(Request $request)
    {
        $user = Auth::user();
        $project_id = $request->input('proyecto');
        $task_id = $request->input('tarea');

        $fichajeActivo = Fichaje::where('user_id', $user->id)
            ->whereNull('fin')
            ->first();

        if ($fichajeActivo) {
            return redirect()->back()->with('error', 'Ya tienes un fichaje activo. Finalízalo antes de iniciar uno nuevo.');
        }

        $tasks = $this->fetchTasksFromZoho($this->zohoService->getAccessToken(), $project_id);
        $tarea = collect($tasks)->firstWhere('id_string', $task_id);
        $tarea_nombre = $tarea['name'] ?? 'Tarea desconocida';

        $proyectos = Cache::get('zoho_proyectos_' . $user->id, []);
        $proyecto = collect($proyectos)->firstWhere('id_string', $project_id);
        $proyecto_nombre = $proyecto['name'] ?? 'Proyecto desconocido';

        $ubicacion = $this->obtenerUbicacion($request);

        $fichaje = Fichaje::create([
            'user_id' => $user->id,
            'project_id' => $project_id,
            'tarea_id' => $task_id,
            'tarea_nombre' => $tarea_nombre,
            'proyecto_nombre' => $proyecto_nombre,
            'inicio' => now(),
            'estado' => 'activo',
            'active_seconds' => 0,
            'paused_seconds' => 0,
            'latitud' => $ubicacion['latitud'],
            'longitud' => $ubicacion['longitud'],
        ]);

        // Invalidar la caché del fichaje activo
        $cacheKey = 'fichaje_activo_' . Auth::id();
        Cache::forget($cacheKey);

        return redirect()->route('dashboard')->with('success', 'Fichaje iniciado correctamente.');
    }

    public function pausarFichaje(Request $request, $fichajeId)
    {
        \Log::info('Inicio de pausarFichaje', ['fichaje_id' => $fichajeId, 'timestamp' => now()->toDateTimeString()]);

        $startTime = microtime(true);

        \Log::info('Buscando fichaje en la base de datos');
        $fichaje = Fichaje::where('user_id', Auth::id())
            ->where('id', $fichajeId)
            ->whereNull('fin')
            ->firstOrFail();

        $timeAfterQuery = microtime(true);
        \Log::info('Fichaje encontrado', [
            'fichaje' => $fichaje->toArray(),
            'time_taken_seconds' => $timeAfterQuery - $startTime
        ]);

        if ($fichaje->estado != 'activo') {
            \Log::warning('El fichaje no está activo', ['estado' => $fichaje->estado]);
            return redirect()->back()->with('error', 'El fichaje no está activo para pausar.');
        }

        \Log::info('Calculando tiempo activo');
        $activeSeconds = (int) $fichaje->active_seconds;
        $now = now();
        \Log::info('Fecha actual (now)', ['now' => $now->toDateTimeString(), 'timezone' => $now->timezone->getName()]);
        \Log::info('Fecha de inicio', ['inicio' => $fichaje->inicio->toDateTimeString(), 'timezone' => $fichaje->inicio->timezone->getName()]);

        if ($fichaje->reanudado) {
            \Log::info('Fecha de reanudado', ['reanudado' => $fichaje->reanudado->toDateTimeString(), 'timezone' => $fichaje->reanudado->timezone->getName()]);
            $secondsSinceReanudado = $fichaje->reanudado->diffInSeconds($now, false);
            $activeSeconds += $secondsSinceReanudado > 0 ? $secondsSinceReanudado : 0;
            \Log::info('Tiempo desde reanudado', ['seconds' => $secondsSinceReanudado]);
        } else {
            $secondsSinceInicio = $fichaje->inicio->diffInSeconds($now, false);
            $activeSeconds += $secondsSinceInicio > 0 ? $secondsSinceInicio : 0;
            \Log::info('Tiempo desde inicio', ['seconds' => $secondsSinceInicio]);
        }

        $timeAfterCalculation = microtime(true);
        \Log::info('Tiempo activo calculado', [
            'active_seconds' => $activeSeconds,
            'time_taken_seconds' => $timeAfterCalculation - $timeAfterQuery
        ]);

        \Log::info('Actualizando fichaje en la base de datos');
        $fichaje->update([
            'pausa' => $now,
            'estado' => 'pausado',
            'active_seconds' => $activeSeconds,
        ]);

        $timeAfterUpdate = microtime(true);
        \Log::info('Fichaje actualizado', [
            'time_taken_seconds' => $timeAfterUpdate - $timeAfterCalculation
        ]);

        // Invalidar la caché del fichaje activo
        $cacheKey = 'fichaje_activo_' . Auth::id();
        Cache::forget($cacheKey);

        \Log::info('Redirigiendo al dashboard');
        $response = redirect()->route('dashboard')->with('success', 'Fichaje pausado correctamente.');

        $endTime = microtime(true);
        \Log::info('Fin de pausarFichaje', [
            'total_time_taken_seconds' => $endTime - $startTime
        ]);

        return $response;
    }

    public function reanudarFichaje(Request $request, $fichajeId)
    {
        \Log::info('Entrando en reanudarFichaje', ['fichaje_id' => $fichajeId]);

        $fichaje = Fichaje::where('user_id', Auth::id())
            ->where('id', $fichajeId)
            ->whereNull('fin')
            ->firstOrFail();

        \Log::info('Fichaje encontrado', ['fichaje' => $fichaje->toArray()]);

        if ($fichaje->estado != 'pausado') {
            \Log::warning('El fichaje no está pausado', ['estado' => $fichaje->estado]);
            return redirect()->back()->with('error', 'El fichaje no está pausado para reanudar.');
        }

        $now = now();
        $pausedSeconds = $fichaje->paused_seconds + $fichaje->pausa->diffInSeconds($now, false);
        $pausedSeconds = $pausedSeconds > 0 ? $pausedSeconds : 0;

        $fichaje->update([
            'reanudado' => $now,
            'estado' => 'activo',
            'paused_seconds' => $pausedSeconds,
        ]);

        \Log::info('Fichaje reanudado', ['fichaje_id' => $fichajeId]);

        // Invalidar la caché del fichaje activo
        $cacheKey = 'fichaje_activo_' . Auth::id();
        Cache::forget($cacheKey);

        return redirect()->route('dashboard')->with('success', 'Fichaje reanudado correctamente.');
    }

    public function finalizarFichaje(Request $request, $fichajeId)
    {
        \Log::info('Entrando en finalizarFichaje', ['fichaje_id' => $fichajeId, 'user_id' => Auth::id()]);

        $fichaje = Fichaje::where('user_id', Auth::id())
            ->where('id', $fichajeId)
            ->whereNull('fin')
            ->firstOrFail();

        \Log::info('Fichaje encontrado', ['fichaje' => $fichaje->toArray()]);

        $activeSeconds = $fichaje->active_seconds;
        $endTime = now();
        if ($fichaje->estado == 'activo') {
            $ultimoInicio = $fichaje->reanudado ?? $fichaje->inicio;
            $secondsSinceUltimoInicio = $ultimoInicio->diffInSeconds($endTime, false);
            $activeSeconds += $secondsSinceUltimoInicio > 0 ? $secondsSinceUltimoInicio : 0;
            \Log::info('Tiempo activo adicional después de reanudar', ['seconds' => $secondsSinceUltimoInicio]);
        }

        \Log::info('Tiempo activo total', ['active_seconds' => $activeSeconds]);

        $ubicacion = $this->obtenerUbicacion($request);

        // Notas proporcionadas por el usuario
        $notes = $request->input('notes', 'Tiempo registrado desde la aplicación de fichajes');

        $fichaje->update([
            'fin' => $endTime,
            'estado' => 'finalizado',
            'active_seconds' => $activeSeconds,
            'latitud' => $ubicacion['latitud'],
            'longitud' => $ubicacion['longitud'],
            'notes' => $notes,
        ]);

        \Log::info('Fichaje finalizado', ['fichaje_id' => $fichajeId]);

        // Enviar el tiempo a Zoho Projects
        try {
            $accessToken = $this->zohoService->getAccessToken();
            $projectId = $fichaje->project_id;
            $taskId = $fichaje->tarea_id;

            $date = $endTime->format('m-d-Y'); // Formato requerido por Zoho: MM-DD-YYYY

            // Obtener el nombre del trabajador (por ejemplo, T2)
            $trabajador = strtoupper(explode('@', Auth::user()->email)[0]);

            // Período de tiempo (inicio y fin) en formato HH:MM AM/PM
            $startTime = $fichaje->inicio->format('h:i A'); // Ejemplo: 02:04 PM
            // Asegurar que end_time sea al menos 1 minuto después de start_time
            $endTimeAdjusted = $fichaje->inicio->copy()->addSeconds(max($activeSeconds, 60)); // Mínimo 1 minuto de diferencia
            $endTimeFormatted = $endTimeAdjusted->format('h:i A'); // Ejemplo: 02:05 PM

            $endpoint = "/portal/{$this->portalName}/projects/{$projectId}/tasks/{$taskId}/logs/";
            $data = [
                'date' => $date,
                'bill_status' => 'Billable',
                'notes' => $notes,
                'start_time' => $startTime,
                'end_time' => $endTimeFormatted,
                'custom_fields' => [
                    'Trabajador' => $trabajador
                ]
            ];

            \Log::info('Datos enviados a Zoho Projects', ['data' => $data]);

            $response = $this->zohoService->post($endpoint, $data);
            \Log::info('Tiempo enviado a Zoho Projects', ['response' => $response]);
        } catch (\Exception $e) {
            \Log::error('Error al enviar tiempo a Zoho Projects', ['message' => $e->getMessage()]);
        }

        // Invalidar la caché del fichaje activo
        $cacheKey = 'fichaje_activo_' . Auth::id();
        Cache::forget($cacheKey);

        return redirect()->route('dashboard')->with('success', 'Fichaje finalizado correctamente.');
    }
}
