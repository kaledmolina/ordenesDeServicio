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
            $table->string('latitud_llegada')->nullable();
            $table->string('longitud_llegada')->nullable();
            $table->timestamp('fecha_llegada')->nullable();

            $table->string('latitud_inicio')->nullable();
            $table->string('longitud_inicio')->nullable();
            
            $table->string('latitud_fin')->nullable();
            $table->string('longitud_fin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordens', function (Blueprint $table) {
            $table->dropColumn([
                'latitud_llegada', 
                'longitud_llegada', 
                'fecha_llegada',
                'latitud_inicio',
                'longitud_inicio',
                'latitud_fin',
                'longitud_fin'
            ]);
        });
    }
};
