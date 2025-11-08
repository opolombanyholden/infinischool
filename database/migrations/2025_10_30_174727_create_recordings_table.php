<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            
            $table->string('title');
            $table->string('file_path'); // Chemin du fichier vidéo
            $table->string('file_url')->nullable(); // URL publique
            $table->bigInteger('file_size')->default(0); // Taille en octets
            $table->integer('duration_seconds')->default(0); // Durée en secondes
            
            // Métadonnées
            $table->string('format')->default('mp4'); // Format vidéo
            $table->string('quality')->default('720p'); // Qualité
            
            // Statistiques
            $table->integer('views_count')->default(0);
            $table->decimal('average_watch_time', 5, 2)->default(0); // Temps de visionnage moyen en minutes
            
            $table->enum('status', ['processing', 'ready', 'failed'])->default('processing');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordings');
    }
};