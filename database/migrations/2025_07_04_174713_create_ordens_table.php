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
        Schema::create('ordens', function (Blueprint $table) {
            $table->id();
            $table->string('numero_orden')->unique();
            $table->string('numero_expediente')->nullable();
            $table->string('nombre_cliente');
            $table->dateTime('fecha_hora');
            $table->decimal('valor_servicio', 10, 2)->nullable();
            $table->string('placa')->nullable();
            $table->string('referencia')->nullable();
            $table->string('nombre_asignado')->nullable();
            $table->string('celular')->nullable();
            
            $table->string('unidad_negocio')->nullable();
            $table->string('movimiento')->nullable();
            $table->string('servicio')->nullable();
            $table->string('modalidad')->nullable();
            $table->string('tipo_activo')->nullable();
            $table->string('marca')->nullable();

            $table->string('ciudad_origen');
            $table->string('direccion_origen');
            $table->text('observaciones_origen')->nullable();

            $table->string('ciudad_destino');
            $table->string('direccion_destino');
            $table->text('observaciones_destino')->nullable();

            $table->text('observaciones_generales')->nullable();
            $table->boolean('es_programada')->default(false);

            // --- Campos que ya tenías y se mantienen ---
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('abierta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens');
    }
};
