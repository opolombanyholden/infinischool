<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('formation_id')->constrained()->onDelete('cascade');
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            
            $table->string('certificate_number')->unique(); // Numéro unique du certificat
            $table->date('issue_date'); // Date d'émission
            
            $table->decimal('final_grade', 5, 2); // Note finale
            $table->string('grade_letter')->nullable(); // Mention (A, B, C, etc.)
            
            $table->string('file_path')->nullable(); // Chemin du PDF
            $table->string('verification_code')->unique(); // Code de vérification
            
            $table->boolean('is_verified')->default(true);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};