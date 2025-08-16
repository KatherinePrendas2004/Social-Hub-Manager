<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Contenido de la publicación
            $table->text('content');
            $table->json('media')->nullable(); // Para imágenes/videos
            
            // Configuración de publicación
            $table->enum('type', ['instant', 'queued', 'scheduled'])->default('instant');
            $table->enum('status', ['draft', 'pending', 'publishing', 'published', 'failed', 'cancelled'])->default('draft');
            
            // Plataformas seleccionadas (JSON array)
            $table->json('platforms'); // ['twitter', 'linkedin', 'reddit']
            
            // Fechas
            $table->timestamp('scheduled_at')->nullable(); // Para publicaciones programadas
            $table->timestamp('published_at')->nullable(); // Cuando se publicó realmente
            
            // Resultados de publicación por plataforma
            $table->json('publish_results')->nullable(); // Resultados por plataforma
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'type']);
            $table->index('scheduled_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};