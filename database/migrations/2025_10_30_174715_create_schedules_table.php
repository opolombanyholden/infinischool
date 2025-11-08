<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            
            $table->string('day_of_week'); // Jour de la semaine (Lundi, Mardi, etc.)
            $table->time('start_time'); // Heure de début
            $table->time('end_time'); // Heure de fin
            $table->string('room')->nullable(); // Salle (virtuelle ou physique)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};