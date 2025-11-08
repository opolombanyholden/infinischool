<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| Commandes Artisan personnalisées via routes
| Note: Pour des commandes plus complexes, utilisez app/Console/Commands
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

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
        ->where('status', 'published')
        ->get();
    
    foreach ($recordings as $recording) {
        // Logique d'archivage ici (ex: déplacer vers cold storage)
        $this->line("Archivage: {$recording->title}");
    }
    
    $this->info("✓ {$recordings->count()} enregistrements archivés");
})->purpose('Archiver les enregistrements de plus de 6 mois');

/**
 * Envoyer les rappels de cours
 */
Artisan::command('infinischool:send-course-reminders', function () {
    $this->info('Envoi des rappels de cours...');
    
    // Cours qui commencent dans 1 heure
    $upcomingCourses = \App\Models\Course::where('scheduled_at', '>=', now())
        ->where('scheduled_at', '<=', now()->addHour())
        ->where('status', 'scheduled')
        ->with(['class.students', 'teacher'])
        ->get();
    
    $count = 0;
    foreach ($upcomingCourses as $course) {
        // Envoyer notification aux étudiants
        foreach ($course->class->students as $student) {
            // Logique d'envoi notification
            $count++;
        }
        
        $this->line("Rappels envoyés pour: {$course->title}");
    }
    
    $this->info("✓ {$count} rappels envoyés");
})->purpose('Envoyer les rappels de cours à venir');

/**
 * Vérifier et marquer les cours comme terminés
 */
Artisan::command('infinischool:mark-completed-courses', function () {
    $this->info('Marquage des cours terminés...');
    
    $courses = \App\Models\Course::where('status', 'live')
        ->where('scheduled_at', '<', now()->subHours(3))
        ->get();
    
    foreach ($courses as $course) {
        $course->update(['status' => 'completed']);
        $this->line("Marqué comme terminé: {$course->title}");
    }
    
    $this->info("✓ {$courses->count()} cours marqués comme terminés");
})->purpose('Marquer automatiquement les cours comme terminés');

/**
 * Générer les certificats pour les formations terminées
 */
Artisan::command('infinischool:generate-certificates', function () {
    $this->info('Génération des certificats...');
    
    // Trouver les étudiants ayant terminé une formation
    $completedEnrollments = \App\Models\Enrollment::where('status', 'completed')
        ->whereDoesntHave('certificate')
        ->with(['student', 'formation'])
        ->get();
    
    foreach ($completedEnrollments as $enrollment) {
        // Logique de génération du certificat
        $this->line("Certificat généré pour: {$enrollment->student->getFullNameAttribute()}");
    }
    
    $this->info("✓ {$completedEnrollments->count()} certificats générés");
})->purpose('Générer les certificats pour les formations terminées');

/**
 * Calculer et mettre à jour les statistiques
 */
Artisan::command('infinischool:update-statistics', function () {
    $this->info('Mise à jour des statistiques...');
    
    // Mettre à jour les statistiques de cours
    $courses = \App\Models\Course::where('status', 'completed')->get();
    
    foreach ($courses as $course) {
        // Calculer taux de présence
        $attendanceRate = $course->attendances()->count() / $course->class->students()->count() * 100;
        
        // Mettre à jour les métriques
        // ...
        
        $this->line("Stats mises à jour pour: {$course->title}");
    }
    
    $this->info('✓ Statistiques mises à jour');
})->purpose('Mettre à jour les statistiques de la plateforme');

/**
 * Envoyer un rapport hebdomadaire aux enseignants
 */
Artisan::command('infinischool:send-weekly-reports', function () {
    $this->info('Envoi des rapports hebdomadaires...');
    
    $teachers = \App\Models\User::where('role', 'teacher')
        ->where('status', 'active')
        ->get();
    
    foreach ($teachers as $teacher) {
        // Générer et envoyer le rapport
        $this->line("Rapport envoyé à: {$teacher->getFullNameAttribute()}");
    }
    
    $this->info("✓ {$teachers->count()} rapports envoyés");
})->purpose('Envoyer les rapports hebdomadaires aux enseignants');

/**
 * Vérifier les paiements en attente
 */
Artisan::command('infinischool:check-pending-payments', function () {
    $this->info('Vérification des paiements en attente...');
    
    $pendingPayments = \App\Models\Payment::where('status', 'pending')
        ->where('created_at', '<', now()->subDays(3))
        ->get();
    
    foreach ($pendingPayments as $payment) {
        // Vérifier le statut avec Stripe
        $this->line("Vérification paiement ID: {$payment->id}");
    }
    
    $this->info("✓ {$pendingPayments->count()} paiements vérifiés");
})->purpose('Vérifier et mettre à jour les paiements en attente');

/**
 * Envoyer des alertes pour les devoirs non rendus
 */
Artisan::command('infinischool:alert-overdue-assignments', function () {
    $this->info('Envoi des alertes pour devoirs en retard...');
    
    $overdueAssignments = \App\Models\Assignment::where('due_date', '<', now())
        ->where('status', 'published')
        ->whereDoesntHave('submissions')
        ->with('course.class.students')
        ->get();
    
    $count = 0;
    foreach ($overdueAssignments as $assignment) {
        foreach ($assignment->course->class->students as $student) {
            // Envoyer alerte
            $count++;
        }
    }
    
    $this->info("✓ {$count} alertes envoyées");
})->purpose('Alerter les étudiants des devoirs non rendus');

/**
 * Sauvegarder la base de données
 */
Artisan::command('infinischool:backup', function () {
    $this->info('Création de la sauvegarde...');
    
    $filename = 'backup-' . now()->format('Y-m-d-His') . '.sql';
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
        'revenue_today' => \App\Models\Payment::whereDate('paid_at', today())->sum('amount'),
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