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
            // Basic Info
            $table->string('estrato')->nullable();
            $table->string('zona')->nullable();
            $table->string('barrio')->nullable();
            
            // Contact
            $table->string('telefono_facturacion')->nullable();
            $table->string('otro_telefono')->nullable();
            
            // Service Info
            $table->string('tipo_servicio')->nullable(); // Residencial, etc.
            $table->string('vendedor')->nullable();
            $table->string('tipo_operacion')->nullable(); // PROPIA, etc.
            
            // Dates
            $table->date('suscripcion_tv')->nullable();
            $table->date('suscripcion_internet')->nullable();
            $table->date('fecha_ultimo_pago')->nullable();
            
            // Statuses
            $table->string('estado_tv')->default('A'); // A = Activo?
            $table->string('estado_internet')->default('A');
            
            // Billing / Balances
            $table->decimal('saldo_tv', 10, 2)->default(0);
            $table->decimal('saldo_internet', 10, 2)->default(0);
            $table->decimal('saldo_otros', 10, 2)->default(0);
            $table->decimal('saldo_total', 10, 2)->default(0);
            
            $table->decimal('tarifa_tv', 10, 2)->default(0);
            $table->decimal('tarifa_internet', 10, 2)->default(0);
            $table->decimal('tarifa_total', 10, 2)->default(0);
            
            // Technical / Plan
            $table->string('plan_internet')->nullable();
            $table->string('velocidad')->nullable();
            
            // Cut/Retire Flags (Assuming strings based on user example showing empty strings, but could be boolean or dates)
            $table->string('cortado_tv')->nullable();
            $table->string('retiro_tv')->nullable();
            $table->string('cortado_int')->nullable();
            $table->string('retiro_int')->nullable();
            
            // Equipment
            $table->string('serial')->nullable();
            $table->string('mac')->nullable();
            $table->string('ip')->nullable();
            $table->string('marca')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'estrato', 'zona', 'barrio', 'telefono_facturacion', 'otro_telefono',
                'tipo_servicio', 'vendedor', 'tipo_operacion',
                'suscripcion_tv', 'suscripcion_internet', 'fecha_ultimo_pago',
                'estado_tv', 'estado_internet',
                'saldo_tv', 'saldo_internet', 'saldo_otros', 'saldo_total',
                'tarifa_tv', 'tarifa_internet', 'tarifa_total',
                'plan_internet', 'velocidad',
                'cortado_tv', 'retiro_tv', 'cortado_int', 'retiro_int',
                'serial', 'mac', 'ip', 'marca'
            ]);
        });
    }
};
