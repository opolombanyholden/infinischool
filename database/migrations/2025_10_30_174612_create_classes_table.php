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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained()->onDelete('cascade');
            
            $table->string('name'); // Nom de la classe (ex: "Classe A - Débutants")
            $table->string('code')->unique(); // Code unique (ex: "INF-2024-A")
            
            // Capacité
            $table->integer('max_students')->default(30); // Nombre maximum d'étudiants
            $table->integer('current_students')->default(0); // Nombre actuel d'étudiants
            
            // Dates
            $table->date('start_date'); // Date de début
            $table->date('end_date'); // Date de fin
            
            // Emploi du temps
            $table->json('schedule')->nullable(); // Emploi du temps (jours et heures)
            
            // Statut
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            
            // Enseignant principal (optionnel, peut être assigné plus tard)
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};