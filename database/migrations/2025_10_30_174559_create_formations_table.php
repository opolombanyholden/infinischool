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
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Titre de la formation
            $table->string('slug')->unique(); // URL friendly
            $table->text('description'); // Description
            $table->text('objectives')->nullable(); // Objectifs
            $table->text('prerequisites')->nullable(); // Prérequis
            
            // Détails formation
            $table->string('level'); // Niveau (débutant, intermédiaire, avancé)
            $table->integer('duration_weeks'); // Durée en semaines
            $table->integer('hours_per_week'); // Heures par semaine
            $table->decimal('price', 10, 2); // Prix
            $table->integer('discount_percentage')->default(0); // Réduction
            
            // Média
            $table->string('image')->nullable(); // Image de couverture
            $table->string('video_preview')->nullable(); // Vidéo de présentation
            
            // Statistiques
            $table->integer('enrolled_count')->default(0); // Nombre d'inscrits
            $table->decimal('rating', 3, 2)->default(0); // Note moyenne
            $table->integer('reviews_count')->default(0); // Nombre d'avis
            
            // Statut
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false); // Formation mise en avant
            
            // Certification
            $table->boolean('certificate_available')->default(true);
            $table->string('certificate_type')->nullable(); // Type de certificat
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};