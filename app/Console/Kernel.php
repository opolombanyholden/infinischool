<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*
        |--------------------------------------------------------------------------
        | Tâches Planifiées InfiniSchool
        |--------------------------------------------------------------------------
        |
        | C'est ICI qu'on définit le planning des commandes.
        | Les méthodes hourly(), daily(), etc. sont utilisées ici.
        |
        */
        
        // Commande inspire (exemple Laravel)
        // $schedule->command('inspire')->hourly();
        
        // ============================================
        // TÂCHES QUOTIDIENNES
        // ============================================
        
        // Nettoyer les anciennes notifications (tous les jours à 2h00)
        $schedule->command('infinischool:clean-notifications')
            ->dailyAt('02:00')
            ->withoutOverlapping();
        
        // Nettoyer les anciennes sessions (tous les jours à 3h00)
        $schedule->command('infinischool:clean-sessions')
            ->dailyAt('03:00')
            ->withoutOverlapping();
        
        // Archiver les anciens enregistrements (tous les jours à 4h00)
        $schedule->command('infinischool:clean-old-recordings')
            ->dailyAt('04:00')
            ->withoutOverlapping();
        
        // Générer les certificats en attente (tous les jours à 10h00)
        $schedule->command('infinischool:generate-certificates')
            ->dailyAt('10:00')
            ->withoutOverlapping();
        
        // Rapport quotidien pour les admins (tous les jours à 8h00)
        $schedule->command('infinischool:daily-admin-report')
            ->dailyAt('08:00')
            ->emailOutputTo('admin@infinischool.com'); // Optionnel: envoyer par email
        
        // Backup quotidien de la base (tous les jours à 1h00)
        $schedule->command('infinischool:backup-db')
            ->dailyAt('01:00')
            ->withoutOverlapping();
        
        // ============================================
        // TÂCHES HORAIRES
        // ============================================
        
        // Envoyer les rappels de cours (toutes les heures)
        $schedule->command('infinischool:send-course-reminders')
            ->hourly()
            ->withoutOverlapping();
        
        // Mettre à jour les statistiques (toutes les heures)
        $schedule->command('infinischool:update-stats')
            ->hourly()
            ->withoutOverlapping();
        
        // Synchroniser avec Zoom (toutes les 6 heures)
        $schedule->command('infinischool:sync-zoom-recordings')
            ->everySixHours()
            ->withoutOverlapping();
        
        // ============================================
        // TÂCHES HEBDOMADAIRES
        // ============================================
        
        // Optimiser la base de données (tous les dimanches à 5h00)
        $schedule->command('infinischool:optimize-db')
            ->weekly()
            ->sundays()
            ->at('05:00')
            ->withoutOverlapping();
        
        // Tester les intégrations (tous les lundis à 9h00)
        $schedule->command('infinischool:test-integrations')
            ->weekly()
            ->mondays()
            ->at('09:00');
        
        // ============================================
        // TÂCHES LARAVEL PAR DÉFAUT
        // ============================================
        
        // Nettoyer les anciennes entrées de la table telescope (si utilisé)
        // $schedule->command('telescope:prune')->daily();
        
        // Nettoyer les jobs échoués de plus de 7 jours
        // $schedule->command('queue:prune-failed --hours=168')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}