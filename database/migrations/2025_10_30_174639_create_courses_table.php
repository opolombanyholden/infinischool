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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            
            $table->string('title'); // Titre du cours
            $table->text('description')->nullable();
            
            // Planification
            $table->dateTime('scheduled_at'); // Date et heure programmées
            $table->integer('duration_minutes')->default(60); // Durée en minutes
            
            // Visioconférence
            $table->string('meeting_url')->nullable(); // Lien Zoom/Teams
            $table->string('meeting_id')->nullable(); // ID de la réunion
            $table->string('meeting_password')->nullable(); // Mot de passe
            
            // Statut du cours
            $table->enum('status', ['scheduled', 'live', 'completed', 'cancelled'])->default('scheduled');
            
            // Présence
            $table->integer('attendance_count')->default(0); // Nombre de présents
            
            // Enregistrement
            $table->boolean('is_recorded')->default(true);
            $table->dateTime('started_at')->nullable(); // Heure de début réelle
            $table->dateTime('ended_at')->nullable(); // Heure de fin réelle
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};