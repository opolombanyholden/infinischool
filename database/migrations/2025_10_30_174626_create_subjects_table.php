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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained()->onDelete('cascade');
            
            $table->string('name'); // Nom de la matière
            $table->string('code')->unique(); // Code de la matière (ex: "MAT-101")
            $table->text('description')->nullable();
            
            // Détails
            $table->integer('total_hours'); // Nombre total d'heures
            $table->integer('order')->default(0); // Ordre d'affichage
            
            // Coefficient
            $table->integer('coefficient')->default(1);
            
            // Enseignant assigné
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};