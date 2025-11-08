<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('formation_id')->constrained()->onDelete('cascade');
            $table->foreignId('enrollment_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('transaction_id')->unique(); // ID de transaction
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('XAF'); // Franc CFA
            
            $table->enum('payment_method', ['stripe', 'paypal', 'bank_transfer', 'cash'])->default('stripe');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            
            $table->dateTime('paid_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};