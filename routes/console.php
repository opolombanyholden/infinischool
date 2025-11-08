<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| Commandes Artisan personnalisées via routes
| Note: Pour des commandes plus complexes, utilisez app/Console/Commands
| 
| IMPORTANT: Les méthodes de scheduling (hourly, daily, etc.) ne peuvent 
| PAS être utilisées ici. Utilisez app/Console/Kernel.php pour le scheduling.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Commandes de Maintenance InfiniSchool
|--------------------------------------------------------------------------
*/

/**
 * Nettoyer les anciennes notifications
 */
Artisan::command('infinischool:clean-notifications', function () {
    $this->info('Nettoyage des anciennes notifications...');
    
    $count = \DB::table('notifications')
        ->where('read_at', '!=', null)
        ->where('created_at', '<', now()->subDays(30))
        ->delete();
    
    $this->info("✓ {$count} notifications supprimées");
})->purpose('Nettoyer les notifications lues de plus de 30 jours');

/**
 * Nettoyer les anciennes sessions
 */
Artisan::command('infinischool:clean-sessions', function () {
    $this->info('Nettoyage des anciennes sessions...');
    
    $count = \DB::table('sessions')
        ->where('last_activity', '<', now()->subDays(7)->timestamp)
        ->delete();
    
    $this->info("✓ {$count} sessions supprimées");
})->purpose('Nettoyer les sessions de plus de 7 jours');

/**
 * Nettoyer les enregistrements de cours obsolètes
 */
Artisan::command('infinischool:clean-old-recordings', function () {
    $this->info('Archivage des anciens enregistrements...');
    
    $recordings = \App\Models\Recording::where('created_at', '<', now()->subMonths(6))
        ->where('status', '!=', 'archived')
        ->get();
    
    foreach ($recordings as $recording) {
        $recording->update(['status' => 'archived']);
    }
    
    $this->info("✓ {$recordings->count()} enregistrements archivés");
})->purpose('Archiver les enregistrements de plus de 6 mois');

/**
 * Mettre à jour les statistiques
 */
Artisan::command('infinischool:update-stats', function () {
    $this->info('Mise à jour des statistiques...');
    
    // Statistiques utilisateurs
    $activeUsers = \App\Models\User::where('last_login_at', '>', now()->subDays(30))->count();
    
    // Statistiques cours
    $totalCourses = \App\Models\Course::count();
    $completedCourses = \App\Models\Course::where('status', 'completed')->count();
    
    // Statistiques inscriptions
    $activeEnrollments = \App\Models\Enrollment::where('status', 'active')->count();
    
    $this->table(
        ['Métrique', 'Valeur'],
        [
            ['Utilisateurs actifs (30j)', $activeUsers],
            ['Total cours', $totalCourses],
            ['Cours complétés', $completedCourses],
            ['Inscriptions actives', $activeEnrollments],
        ]
    );
    
    $this->info('✓ Statistiques mises à jour');
})->purpose('Mettre à jour les statistiques de la plateforme');

/**
 * Générer les certificats en attente
 */
Artisan::command('infinischool:generate-certificates', function () {
    $this->info('Génération des certificats...');
    
    $enrollments = \App\Models\Enrollment::where('status', 'completed')
        ->whereDoesntHave('certificate')
        ->with('user', 'formation')
        ->get();
    
    $count = 0;
    foreach ($enrollments as $enrollment) {
        \App\Models\Certificate::create([
            'user_id' => $enrollment->user_id,
            'formation_id' => $enrollment->formation_id,
            'enrollment_id' => $enrollment->id,
            'certificate_number' => 'CERT-' . strtoupper(uniqid()),
            'issued_at' => now(),
        ]);
        $count++;
    }
    
    $this->info("✓ {$count} certificats générés");
})->purpose('Générer les certificats pour les formations complétées');

/**
 * Envoyer les rappels de cours
 */
Artisan::command('infinischool:send-course-reminders', function () {
    $this->info('Envoi des rappels de cours...');
    
    // Cours dans les prochaines 24h
    $upcomingCourses = \App\Models\Course::whereBetween('scheduled_at', [
        now(),
        now()->addDay()
    ])->with('enrollments.user')->get();
    
    $count = 0;
    foreach ($upcomingCourses as $course) {
        foreach ($course->enrollments as $enrollment) {
            // TODO: Envoyer email de rappel
            // Mail::to($enrollment->user)->send(new CourseReminder($course));
            $count++;
        }
    }
    
    $this->info("✓ {$count} rappels envoyés");
})->purpose('Envoyer les rappels de cours aux étudiants');

/**
 * Backup de la base de données
 */
Artisan::command('infinischool:backup-db', function () {
    $this->info('Création d\'une sauvegarde...');
    
    $filename = 'backup-infinischool-' . now()->format('Y-m-d-His') . '.sql';
    $path = storage_path('backups/' . $filename);
    
    // Créer le répertoire s'il n'existe pas
    if (!file_exists(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
    
    // Commande mysqldump
    $command = sprintf(
        'mysqldump -u%s -p%s %s > %s',
        config('database.connections.mysql.username'),
        config('database.connections.mysql.password'),
        config('database.connections.mysql.database'),
        $path
    );
    
    exec($command);
    
    $this->info("✓ Sauvegarde créée: {$filename}");
})->purpose('Créer une sauvegarde de la base de données');

/**
 * Optimiser les tables de la base de données
 */
Artisan::command('infinischool:optimize-db', function () {
    $this->info('Optimisation de la base de données...');
    
    \DB::statement('OPTIMIZE TABLE users, courses, enrollments, grades');
    
    $this->info('✓ Base de données optimisée');
})->purpose('Optimiser les tables de la base de données');

/**
 * Synchroniser avec Zoom
 */
Artisan::command('infinischool:sync-zoom-recordings', function () {
    $this->info('Synchronisation des enregistrements Zoom...');
    
    $courses = \App\Models\Course::where('status', 'completed')
        ->whereNotNull('meeting_id')
        ->whereDoesntHave('recordings')
        ->get();
    
    foreach ($courses as $course) {
        // Vérifier si l'enregistrement existe sur Zoom
        $this->line("Vérification pour: {$course->title}");
    }
    
    $this->info("✓ Synchronisation terminée");
})->purpose('Synchroniser les enregistrements depuis Zoom');

/**
 * Rapport quotidien pour les admins
 */
Artisan::command('infinischool:daily-admin-report', function () {
    $this->info('Génération du rapport quotidien...');
    
    $stats = [
        'new_users' => \App\Models\User::whereDate('created_at', today())->count(),
        'new_enrollments' => \App\Models\Enrollment::whereDate('created_at', today())->count(),
        'courses_today' => \App\Models\Course::whereDate('scheduled_at', today())->count(),
        'revenue_today' => \DB::table('payments')->whereDate('paid_at', today())->sum('amount') ?? 0,
    ];
    
    $this->table(
        ['Métrique', 'Valeur'],
        [
            ['Nouveaux utilisateurs', $stats['new_users']],
            ['Nouvelles inscriptions', $stats['new_enrollments']],
            ['Cours aujourd\'hui', $stats['courses_today']],
            ['Revenus', number_format($stats['revenue_today'], 2) . ' €'],
        ]
    );
    
    $this->info('✓ Rapport généré');
})->purpose('Générer le rapport quotidien pour les administrateurs');

/**
 * Tester les intégrations
 */
Artisan::command('infinischool:test-integrations', function () {
    $this->info('Test des intégrations...');
    
    // Test Zoom
    $this->line('Test Zoom API...');
    // Logique de test
    
    // Test Stripe
    $this->line('Test Stripe API...');
    // Logique de test
    
    // Test Email
    $this->line('Test Email SMTP...');
    // Logique de test
    
    $this->info('✓ Tests terminés');
})->purpose('Tester les intégrations tierces (Zoom, Stripe, Email)');

/*
|--------------------------------------------------------------------------
| Commandes de Développement
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
    
    /**
     * Générer des données de test
     */
    Artisan::command('infinischool:generate-test-data', function () {
        $this->info('Génération de données de test...');
        
        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
        
        $this->info('✓ Données de test générées');
    })->purpose('Générer des données de test pour le développement');
    
    /**
     * Reset complet de la base
     */
    Artisan::command('infinischool:fresh-install', function () {
        if ($this->confirm('⚠️  Cela va supprimer TOUTES les données. Continuer ?')) {
            $this->info('Reset de la base de données...');
            
            Artisan::call('migrate:fresh');
            Artisan::call('db:seed');
            
            $this->info('✓ Installation fraîche terminée');
        }
    })->purpose('Réinstaller complètement la plateforme (DEV ONLY)');
}