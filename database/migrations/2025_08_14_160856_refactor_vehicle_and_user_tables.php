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
        // Añade las nuevas columnas a la tabla de vehículos
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('tarjeta_propiedad')->nullable()->after('marca');
            $table->date('fecha_tecnomecanica')->nullable()->after('tarjeta_propiedad');
            $table->date('fecha_soat')->nullable()->after('fecha_tecnomecanica');
            $table->string('mantenimiento_preventivo_taller')->nullable()->after('fecha_soat');
            $table->date('fecha_mantenimiento')->nullable()->after('mantenimiento_preventivo_taller');
            $table->date('fecha_ultimo_aceite')->nullable()->after('fecha_mantenimiento');
        });

        // Elimina las columnas antiguas de la tabla de usuarios
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tarjeta_propiedad',
                'fecha_tecnomecanica',
                'fecha_soat',
                'mantenimiento_preventivo_taller',
                'fecha_mantenimiento',
                'fecha_ultimo_aceite',
                'vehiculo', // Se elimina el campo de texto antiguo
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revierte los cambios si es necesario
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'tarjeta_propiedad',
                'fecha_tecnomecanica',
                'fecha_soat',
                'mantenimiento_preventivo_taller',
                'fecha_mantenimiento',
                'fecha_ultimo_aceite',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('tarjeta_propiedad')->nullable();
            $table->date('fecha_tecnomecanica')->nullable();
            $table->date('fecha_soat')->nullable();
            $table->string('mantenimiento_preventivo_taller')->nullable();
            $table->date('fecha_mantenimiento')->nullable();
            $table->date('fecha_ultimo_aceite')->nullable();
            $table->string('vehiculo')->nullable();
        });
    }
};