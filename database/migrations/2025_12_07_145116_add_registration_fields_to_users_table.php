<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Formation souhaitée (pour étudiants)
            $table->foreignId('desired_formation_id')
                ->nullable()
                ->after('role')
                ->constrained('formations')
                ->onDelete('set null');

            // Diplôme/Niveau visé
            $table->enum('desired_level', ['bac', 'licence', 'master', 'doctorat', 'autre'])
                ->nullable()
                ->after('desired_formation_id');

            // Statut d'inscription
            $table->enum('registration_status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->after('status')
                ->comment('Statut de validation de l\'inscription');

            // Date de validation
            $table->timestamp('approved_at')->nullable()->after('registration_status');

            // Validé par (admin)
            $table->foreignId('approved_by')
                ->nullable()
                ->after('approved_at')
                ->constrained('users')
                ->onDelete('set null');

            // Raison du refus
            $table->text('rejection_reason')->nullable()->after('approved_by');

            // Rendre phone et address obligatoires pour étudiants (validation app)
            // On garde nullable au niveau BDD mais validation Laravel obligatoire
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['desired_formation_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'desired_formation_id',
                'desired_level',
                'registration_status',
                'approved_at',
                'approved_by',
                'rejection_reason',
            ]);
        });
    }
};
