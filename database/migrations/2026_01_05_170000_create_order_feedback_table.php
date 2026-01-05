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
        Schema::create('order_feedback', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('orden_id')->constrained('ordens')->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Core Metric
            $table->unsignedTinyInteger('rating'); // 1 to 5 Stars
            
            // Detailed Experience Questions (Yes/No)
            $table->boolean('arrived_on_time')->default(true); // ¿Llegó puntual?
            $table->boolean('is_friendly')->default(true); // ¿Fue amable?
            $table->boolean('problem_solved')->default(true); // ¿Resolvió el problema?
            $table->boolean('wears_uniform')->default(true); // ¿Tenía uniforme/carnet? (Presentación)
            $table->boolean('left_clean')->default(true); // ¿Dejó limpio?
            
            // Open Feedback
            $table->text('comment')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_feedback');
    }
};
