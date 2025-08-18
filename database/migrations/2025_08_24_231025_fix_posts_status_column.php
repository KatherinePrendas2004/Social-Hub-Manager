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
        Schema::table('posts', function (Blueprint $table) {
            // Cambiar la columna status para que soporte todos los valores necesarios
            $table->enum('status', [
                'draft', 
                'queued', 
                'scheduled', 
                'publishing', 
                'published', 
                'failed'
            ])->change();
            
            // O si prefieres usar string con suficiente longitud:
            // $table->string('status', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Revertir a la definición anterior si es necesario
            $table->string('status', 10)->change();
        });
    }
};