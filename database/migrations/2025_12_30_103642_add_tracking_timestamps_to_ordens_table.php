<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ordens', function (Blueprint $table) {
            $table->dateTime('fecha_asignacion')->nullable();
            $table->dateTime('fecha_inicio_atencion')->nullable();
            $table->dateTime('fecha_fin_atencion')->nullable();
            $table->dateTime('fecha_cierre')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordens', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_asignacion',
                'fecha_inicio_atencion',
                'fecha_fin_atencion',
                'fecha_cierre',
            ]);
        });
    }
};
