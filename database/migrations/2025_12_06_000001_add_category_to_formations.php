<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour ajouter la colonne category à la table formations
 * Cette migration corrige l'erreur SQLSTATE[42S22]: Column not found: 1054 Unknown column 'category'
 * La colonne category est utilisée dans:
 * - FormationController::index() pour les filtres
 * - FormationController::category() pour le catalogue par catégorie  
 * - AdminFormationController::index() pour les filtres admin
 * - Les vues Blade pour l'affichage des catégories
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('formations', function (Blueprint $table) {
            // Ajouter la colonne category après le champ level
            $table->string('category')->nullable()->after('level');
            
            // Ajouter un index pour améliorer les performances des filtres
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formations', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }
};