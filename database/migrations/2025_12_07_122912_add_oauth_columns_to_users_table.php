<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Ajoute les colonnes nécessaires pour l'authentification OAuth
     * et les informations de connexion.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Colonnes OAuth (si pas déjà présentes)
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable()->after('password')
                    ->comment('Provider OAuth: google, linkedin, facebook, github');
            }
            
            if (!Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable()->after('provider')
                    ->comment('ID unique de l\'utilisateur chez le provider');
            }
            
            // Avatar
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('provider_id')
                    ->comment('URL de l\'avatar utilisateur');
            }
            
            // Informations de connexion
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('updated_at')
                    ->comment('Date/heure de la dernière connexion');
            }
            
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at')
                    ->comment('Adresse IP de la dernière connexion');
            }
            
            // Statut utilisateur (si pas déjà présent)
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role')
                    ->comment('Indique si le compte est actif');
            }
            
            // Index pour optimiser les recherches OAuth
            $table->index(['provider', 'provider_id'], 'users_oauth_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer l'index
            $table->dropIndex('users_oauth_index');
            
            // Supprimer les colonnes
            $columns = [
                'provider',
                'provider_id', 
                'avatar',
                'last_login_at',
                'last_login_ip',
                'is_active'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};