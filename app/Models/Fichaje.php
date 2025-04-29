<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fichaje extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'tarea_id',
        'tarea_nombre',
        'proyecto_nombre',
        'inicio',
        'pausa',
        'reanudado',
        'fin',
        'estado',
        'latitud',
        'longitud',
        'paused_seconds',
        'active_seconds',
        'notes', // Añadimos notes
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'pausa' => 'datetime',
        'reanudado' => 'datetime',
        'fin' => 'datetime',
        'latitud' => 'float',
        'longitud' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
