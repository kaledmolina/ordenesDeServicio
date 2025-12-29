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
        Schema::table('users', function (Blueprint $table) {
            $table->string('tarjeta_propiedad')->nullable()->after('vehicle_id');
            $table->date('fecha_tecnomecanica')->nullable()->after('tarjeta_propiedad');
            $table->date('fecha_soat')->nullable()->after('fecha_tecnomecanica');
            $table->string('mantenimiento_preventivo_taller')->nullable()->after('fecha_soat');
            $table->date('fecha_mantenimiento')->nullable()->after('mantenimiento_preventivo_taller');
            $table->string('licencia_conduccion')->nullable()->after('fecha_mantenimiento');
            $table->date('fecha_ultimo_aceite')->nullable()->after('licencia_conduccion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tarjeta_propiedad',
                'fecha_tecnomecanica',
                'fecha_soat',
                'mantenimiento_preventivo_taller',
                'fecha_mantenimiento',
                'licencia_conduccion',
                'fecha_ultimo_aceite',
            ]);
        });
    }
};