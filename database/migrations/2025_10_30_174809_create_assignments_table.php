<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            
            $table->string('title');
            $table->text('description');
            $table->text('instructions')->nullable();
            
            $table->dateTime('due_date'); // Date limite
            $table->integer('max_score')->default(20);
            
            $table->string('attachment_path')->nullable(); // Fichier joint
            
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};