<?php

use App\Http\Controllers\FichajeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\ZohoService;

Route::get('/test-zoho', function () {
    $zohoService = app(ZohoService::class);
    $token = $zohoService->getAccessToken();
    return response()->json(['access_token' => $token]);
});

Route::get('/', function () {
    return view('welcome-app');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $cacheKey = 'fichaje_activo_' . auth()->id();
        $fichajeActivo = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return auth()->user()->fichajes()
                ->whereNull('fin')
                ->select('id', 'proyecto_nombre', 'tarea_nombre', 'inicio', 'pausa', 'reanudado', 'estado', 'active_seconds', 'latitud', 'longitud')
                ->first();
        });

        return view('dashboard', compact('fichajeActivo'));
    })->name('dashboard');

    Route::get('/fichaje/historial', function () {
        $historialFichajes = auth()->user()->fichajes()
            ->whereNotNull('fin')
            ->orderBy('fin', 'desc')
            ->take(10)
            ->get(['id', 'proyecto_nombre', 'tarea_nombre', 'inicio', 'fin', 'active_seconds', 'latitud', 'longitud', 'estado', 'notes']);

        return view('fichaje.historial', compact('historialFichajes'));
    })->name('fichaje.historial');

    Route::get('/fichar', [FichajeController::class, 'seleccionarTarea'])->name('fichar.seleccionar');
    Route::get('/fichaje/tareas', [FichajeController::class, 'obtenerTareas'])->name('obtener.tareas');
    Route::post('/fichaje/iniciar', [FichajeController::class, 'iniciarFichaje'])->name('fichaje.iniciar');
    Route::post('/fichaje/pausar/{fichaje}', [FichajeController::class, 'pausarFichaje'])->name('fichaje.pausar');
    Route::post('/fichaje/reanudar/{fichaje}', [FichajeController::class, 'reanudarFichaje'])->name('fichaje.reanudar');
    Route::post('/fichaje/finalizar/{fichaje}', [FichajeController::class, 'finalizarFichaje'])->name('fichaje.finalizar');
    Route::get('/fichaje/datos', [FichajeController::class, 'verDatos'])->name('fichaje.datos');
    Route::post('/fichaje/eliminar-ubicacion/{fichaje}', [FichajeController::class, 'eliminarUbicacion'])->name('fichaje.eliminar-ubicacion');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de administración
    Route::middleware([\App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {
        Route::get('/admin/dashboard', function () {
            $users = \App\Models\User::all();
            $totalFichajes = \App\Models\Fichaje::count();
            $totalTiempoFichado = \App\Models\Fichaje::sum('active_seconds');
            $proyectosActivos = \App\Models\Fichaje::select('proyecto_nombre')
                ->groupBy('proyecto_nombre')
                ->pluck('proyecto_nombre');

            return view('admin.dashboard', compact('users', 'totalFichajes', 'totalTiempoFichado', 'proyectosActivos'));
        })->name('admin.dashboard');

        Route::get('/admin/users/create', function () {
            return view('admin.users.create');
        })->name('admin.users.create');

        Route::post('/admin/users', function (Request $request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'is_admin' => 'boolean',
            ]);

            \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                'is_admin' => $validated['is_admin'] ?? false,
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Usuario creado correctamente.');
        })->name('admin.users.store');

        Route::get('/admin/users/{user}/edit', function (\App\Models\User $user) {
            return view('admin.users.edit', compact('user'));
        })->name('admin.users.edit');

        Route::patch('/admin/users/{user}', function (Request $request, \App\Models\User $user) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8|confirmed',
                'is_admin' => 'boolean',
            ]);

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_admin' => $validated['is_admin'] ?? false,
            ]);

            if ($validated['password']) {
                $user->update(['password' => \Illuminate\Support\Facades\Hash::make($validated['password'])]);
            }

            return redirect()->route('admin.dashboard')->with('success', 'Usuario actualizado correctamente.');
        })->name('admin.users.update');

        Route::delete('/admin/users/{user}', function (\App\Models\User $user) {
            $user->delete();
            return redirect()->route('admin.dashboard')->with('success', 'Usuario eliminado correctamente.');
        })->name('admin.users.destroy');
    });
});

require __DIR__.'/auth.php';
