<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fichajes', function (Blueprint $table) {
            $table->bigInteger('active_seconds')->nullable()->change();
            $table->bigInteger('paused_seconds')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('fichajes', function (Blueprint $table) {
            $table->integer('active_seconds')->nullable()->change();
            $table->integer('paused_seconds')->nullable()->change();
        });
    }
};
