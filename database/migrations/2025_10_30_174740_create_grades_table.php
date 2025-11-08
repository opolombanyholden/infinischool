<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            
            $table->string('assessment_type'); // Type (Examen, Devoir, Quiz, Projet)
            $table->string('assessment_title'); // Titre de l'évaluation
            
            $table->decimal('score', 5, 2); // Note obtenue
            $table->decimal('max_score', 5, 2)->default(20); // Note maximale
            $table->decimal('percentage', 5, 2); // Pourcentage
            
            $table->text('feedback')->nullable(); // Commentaires de l'enseignant
            $table->date('assessment_date'); // Date de l'évaluation
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};