<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fichajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('project_id');
            $table->string('tarea_id');
            $table->string('tarea_nombre');
            $table->string('proyecto_nombre');
            $table->timestamp('inicio')->nullable();
            $table->timestamp('pausa')->nullable();
            $table->timestamp('reanudado')->nullable();
            $table->timestamp('fin')->nullable();
            $table->string('estado'); // 'activo', 'pausado', 'finalizado'
            $table->float('latitud')->nullable();
            $table->float('longitud')->nullable();
            $table->unsignedInteger('paused_seconds')->default(0);
            $table->unsignedInteger('active_seconds')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fichajes');
    }
};