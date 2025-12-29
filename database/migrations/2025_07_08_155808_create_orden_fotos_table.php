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
        Schema::create('orden_fotos', function (Blueprint $table) {
            $table->id();
            // Crea la relación con la tabla de órdenes
            $table->foreignId('orden_id')->constrained()->cascadeOnDelete();
            // Columna para guardar la ruta de la imagen
            $table->string('path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_fotos');
    }
};
