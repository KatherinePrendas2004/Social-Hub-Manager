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
        Schema::create('publish_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['user_id', 'day_of_week', 'is_active']);
            $table->index(['day_of_week', 'time', 'is_active']);
            
            // Un usuario puede tener múltiples horarios para el mismo día
            // pero no el mismo horario exacto (día + hora)
            $table->unique(['user_id', 'day_of_week', 'time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publish_schedules');
    }
};
