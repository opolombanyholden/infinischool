<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;

// Controllers Étudiant
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Student\ScheduleController as StudentScheduleController;
use App\Http\Controllers\Student\ProgressController as StudentProgressController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\ResourceController as StudentResourceController;
use App\Http\Controllers\Student\RecordingController as StudentRecordingController;
use App\Http\Controllers\Student\GradeController as StudentGradeController;
use App\Http\Controllers\Student\CertificateController as StudentCertificateController;
use App\Http\Controllers\Student\CommunityController as StudentCommunityController;
use App\Http\Controllers\Student\SupportController as StudentSupportController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;

// Controllers Enseignant
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\ScheduleController as TeacherScheduleController;
use App\Http\Controllers\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Teacher\ResourceController as TeacherResourceController;
use App\Http\Controllers\Teacher\GradeController as TeacherGradeController;
use App\Http\Controllers\Teacher\AnalyticsController as TeacherAnalyticsController;
use App\Http\Controllers\Teacher\MessageController as TeacherMessageController;
use App\Http\Controllers\Teacher\RecordingController as TeacherRecordingController;
use App\Http\Controllers\Teacher\ProfileController as TeacherProfileController;

// Controllers Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\FormationController as AdminFormationController;
use App\Http\Controllers\Admin\ClassController as AdminClassController;
use App\Http\Controllers\Admin\TeacherController as AdminTeacherController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\FinanceController as AdminFinanceController;
use App\Http\Controllers\Admin\SystemController as AdminSystemController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;

/*
|--------------------------------------------------------------------------
| Routes Publiques
|--------------------------------------------------------------------------
|
| Routes accessibles sans authentification
|
*/

// Page d'accueil
Route::get('/', [HomeController::class, 'index'])->name('home');

// Pages d'information
Route::get('/a-propos', [HomeController::class, 'about'])->name('about');
Route::get('/enseignants', [HomeController::class, 'teachers'])->name('teachers');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'sendContact'])->name('contact.send');

// Formations
Route::get('/formations', [FormationController::class, 'index'])->name('formations.index');
Route::get('/formations/{formation:slug}', [FormationController::class, 'show'])->name('formations.show');

// Pages légales
Route::get('/mentions-legales', [HomeController::class, 'legal'])->name('legal');
Route::get('/conditions-generales', [HomeController::class, 'terms'])->name('terms');
Route::get('/politique-confidentialite', [HomeController::class, 'privacy'])->name('privacy');

/*
|--------------------------------------------------------------------------
| Routes d'Authentification
|--------------------------------------------------------------------------
|
| Routes pour login, register, password reset, email verification
|
*/

// Connexion
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Inscription
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Mot de passe oublié
Route::get('/password/reset', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');

// Vérification email
Route::get('/email/verify', [AuthController::class, 'showVerifyEmailNotice'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])->name('verification.resend');

/*
|--------------------------------------------------------------------------
| Routes Authentifiées
|--------------------------------------------------------------------------
|
| Routes communes à tous les utilisateurs authentifiés
|
*/

Route::middleware(['auth'])->group(function () {
    
    // Dashboard - Redirection selon rôle
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Messagerie (accessible à tous les rôles)
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/create', [MessageController::class, 'create'])->name('create');
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::get('/{message}', [MessageController::class, 'show'])->name('show');
        Route::post('/{message}/reply', [MessageController::class, 'reply'])->name('reply');
        Route::delete('/{message}', [MessageController::class, 'destroy'])->name('destroy');
        Route::post('/{message}/mark-read', [MessageController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [MessageController::class, 'markAllAsRead'])->name('mark-all-read');
    });
    
    // Notifications (accessible à tous les rôles)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });
    
});

/*
|--------------------------------------------------------------------------
| Routes Espace Étudiant
|--------------------------------------------------------------------------
|
| Middleware: auth, verified, role:student
|
*/

Route::prefix('student')->name('student.')->middleware(['auth', 'verified', 'role:student'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    
    // Mes Cours
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', [StudentCourseController::class, 'index'])->name('index');
        Route::get('/{course}', [StudentCourseController::class, 'show'])->name('show');
        Route::post('/{course}/join', [StudentCourseController::class, 'join'])->name('join');
        Route::get('/{course}/live', [StudentCourseController::class, 'live'])->name('live');
    });
    
    // Planning / Emploi du temps
    Route::get('/schedule', [StudentScheduleController::class, 'index'])->name('schedule');
    Route::get('/schedule/export', [StudentScheduleController::class, 'export'])->name('schedule.export');
    
    // Progression
    Route::get('/progress', [StudentProgressController::class, 'index'])->name('progress');
    Route::get('/progress/{subject}', [StudentProgressController::class, 'show'])->name('progress.show');
    
    // Devoirs / Assignments
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [StudentAssignmentController::class, 'index'])->name('index');
        Route::get('/{assignment}', [StudentAssignmentController::class, 'show'])->name('show');
        Route::post('/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('submit');
        Route::get('/{assignment}/download', [StudentAssignmentController::class, 'download'])->name('download');
    });
    
    // Ressources
    Route::prefix('resources')->name('resources.')->group(function () {
        Route::get('/', [StudentResourceController::class, 'index'])->name('index');
        Route::get('/{resource}/download', [StudentResourceController::class, 'download'])->name('download');
        Route::get('/{resource}/view', [StudentResourceController::class, 'view'])->name('view');
    });
    
    // Replay / Enregistrements
    Route::prefix('replay')->name('replay.')->group(function () {
        Route::get('/', [StudentRecordingController::class, 'index'])->name('index');
        Route::get('/{recording}', [StudentRecordingController::class, 'show'])->name('show');
        Route::get('/{recording}/watch', [StudentRecordingController::class, 'watch'])->name('watch');
    });
    
    // Notes / Grades
    Route::prefix('grades')->name('grades.')->group(function () {
        Route::get('/', [StudentGradeController::class, 'index'])->name('index');
        Route::get('/subject/{subject}', [StudentGradeController::class, 'bySubject'])->name('by-subject');
        Route::get('/export', [StudentGradeController::class, 'export'])->name('export');
    });
    
    // Certificats
    Route::prefix('certificates')->name('certificates.')->group(function () {
        Route::get('/', [StudentCertificateController::class, 'index'])->name('index');
        Route::get('/{certificate}/download', [StudentCertificateController::class, 'download'])->name('download');
        Route::get('/{certificate}/view', [StudentCertificateController::class, 'view'])->name('view');
    });
    
    // Communauté / Forum
    Route::prefix('community')->name('community.')->group(function () {
        Route::get('/', [StudentCommunityController::class, 'index'])->name('index');
        Route::get('/topic/{topic}', [StudentCommunityController::class, 'show'])->name('show');
        Route::post('/topic', [StudentCommunityController::class, 'createTopic'])->name('create-topic');
        Route::post('/topic/{topic}/reply', [StudentCommunityController::class, 'reply'])->name('reply');
    });
    
    // Support / Centre d'aide
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [StudentSupportController::class, 'index'])->name('index');
        Route::get('/create', [StudentSupportController::class, 'create'])->name('create');
        Route::post('/', [StudentSupportController::class, 'store'])->name('store');
        Route::get('/{ticket}', [StudentSupportController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [StudentSupportController::class, 'reply'])->name('reply');
    });
    
    // Profil
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [StudentProfileController::class, 'index'])->name('index');
        Route::get('/edit', [StudentProfileController::class, 'edit'])->name('edit');
        Route::put('/', [StudentProfileController::class, 'update'])->name('update');
        Route::put('/password', [StudentProfileController::class, 'updatePassword'])->name('update-password');
        Route::post('/avatar', [StudentProfileController::class, 'updateAvatar'])->name('update-avatar');
    });
    
});

/*
|--------------------------------------------------------------------------
| Routes Espace Enseignant
|--------------------------------------------------------------------------
|
| Middleware: auth, verified, role:teacher
|
*/

Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'verified', 'role:teacher'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    
    // Planning / Emploi du temps
    Route::get('/schedule', [TeacherScheduleController::class, 'index'])->name('schedule');
    Route::get('/schedule/export', [TeacherScheduleController::class, 'export'])->name('schedule.export');
    
    // Programmer / Gérer les cours
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', [TeacherCourseController::class, 'index'])->name('index');
        Route::get('/create', [TeacherCourseController::class, 'create'])->name('create');
        Route::post('/', [TeacherCourseController::class, 'store'])->name('store');
        Route::get('/{course}', [TeacherCourseController::class, 'show'])->name('show');
        Route::get('/{course}/edit', [TeacherCourseController::class, 'edit'])->name('edit');
        Route::put('/{course}', [TeacherCourseController::class, 'update'])->name('update');
        Route::delete('/{course}', [TeacherCourseController::class, 'destroy'])->name('destroy');
        
        // Actions spécifiques
        Route::post('/{course}/start', [TeacherCourseController::class, 'start'])->name('start');
        Route::post('/{course}/end', [TeacherCourseController::class, 'end'])->name('end');
        Route::get('/{course}/live', [TeacherCourseController::class, 'live'])->name('live');
        Route::post('/{course}/generate-zoom', [TeacherCourseController::class, 'generateZoomLink'])->name('generate-zoom');
        Route::post('/{course}/start-recording', [TeacherCourseController::class, 'startRecording'])->name('start-recording');
        Route::post('/{course}/stop-recording', [TeacherCourseController::class, 'stopRecording'])->name('stop-recording');
        Route::get('/{course}/attendance', [TeacherCourseController::class, 'attendance'])->name('attendance');
        Route::post('/{course}/attendance', [TeacherCourseController::class, 'saveAttendance'])->name('save-attendance');
    });
    
    // Mes Classes
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [TeacherClassController::class, 'index'])->name('index');
        Route::get('/{class}', [TeacherClassController::class, 'show'])->name('show');
        Route::get('/{class}/students', [TeacherClassController::class, 'students'])->name('students');
        Route::get('/{class}/export', [TeacherClassController::class, 'exportStudents'])->name('export-students');
    });
    
    // Contenus / Ressources
    Route::prefix('resources')->name('resources.')->group(function () {
        Route::get('/', [TeacherResourceController::class, 'index'])->name('index');
        Route::get('/create', [TeacherResourceController::class, 'create'])->name('create');
        Route::post('/', [TeacherResourceController::class, 'store'])->name('store');
        Route::get('/{resource}', [TeacherResourceController::class, 'show'])->name('show');
        Route::delete('/{resource}', [TeacherResourceController::class, 'destroy'])->name('destroy');
        Route::get('/{resource}/download', [TeacherResourceController::class, 'download'])->name('download');
    });
    
    // Assignments / Devoirs
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [TeacherCourseController::class, 'assignments'])->name('index');
        Route::get('/create', [TeacherCourseController::class, 'createAssignment'])->name('create');
        Route::post('/', [TeacherCourseController::class, 'storeAssignment'])->name('store');
        Route::get('/{assignment}', [TeacherCourseController::class, 'showAssignment'])->name('show');
        Route::get('/{assignment}/edit', [TeacherCourseController::class, 'editAssignment'])->name('edit');
        Route::put('/{assignment}', [TeacherCourseController::class, 'updateAssignment'])->name('update');
        Route::delete('/{assignment}', [TeacherCourseController::class, 'destroyAssignment'])->name('destroy');
        Route::get('/{assignment}/submissions', [TeacherCourseController::class, 'submissions'])->name('submissions');
        Route::post('/{submission}/grade', [TeacherCourseController::class, 'gradeSubmission'])->name('grade');
    });
    
    // Notes / Grades
    Route::prefix('grades')->name('grades.')->group(function () {
        Route::get('/', [TeacherGradeController::class, 'index'])->name('index');
        Route::get('/class/{class}', [TeacherGradeController::class, 'byClass'])->name('by-class');
        Route::get('/subject/{subject}', [TeacherGradeController::class, 'bySubject'])->name('by-subject');
        Route::post('/', [TeacherGradeController::class, 'store'])->name('store');
        Route::put('/{grade}', [TeacherGradeController::class, 'update'])->name('update');
        Route::delete('/{grade}', [TeacherGradeController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-update', [TeacherGradeController::class, 'bulkUpdate'])->name('bulk-update');
        Route::get('/export', [TeacherGradeController::class, 'export'])->name('export');
    });
    
    // Analytics / Statistiques
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [TeacherAnalyticsController::class, 'index'])->name('index');
        Route::get('/engagement', [TeacherAnalyticsController::class, 'engagement'])->name('engagement');
        Route::get('/attendance', [TeacherAnalyticsController::class, 'attendance'])->name('attendance');
        Route::get('/performance', [TeacherAnalyticsController::class, 'performance'])->name('performance');
        Route::get('/class/{class}', [TeacherAnalyticsController::class, 'byClass'])->name('by-class');
    });
    
    // Enregistrements
    Route::prefix('recordings')->name('recordings.')->group(function () {
        Route::get('/', [TeacherRecordingController::class, 'index'])->name('index');
        Route::get('/{recording}', [TeacherRecordingController::class, 'show'])->name('show');
        Route::delete('/{recording}', [TeacherRecordingController::class, 'destroy'])->name('destroy');
        Route::post('/{recording}/publish', [TeacherRecordingController::class, 'publish'])->name('publish');
        Route::post('/{recording}/unpublish', [TeacherRecordingController::class, 'unpublish'])->name('unpublish');
    });
    
    // Messages (route spécifique enseignant)
    Route::get('/messages', [TeacherMessageController::class, 'index'])->name('messages.index');
    
    // Profil
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [TeacherProfileController::class, 'index'])->name('index');
        Route::get('/edit', [TeacherProfileController::class, 'edit'])->name('edit');
        Route::put('/', [TeacherProfileController::class, 'update'])->name('update');
        Route::put('/password', [TeacherProfileController::class, 'updatePassword'])->name('update-password');
        Route::post('/avatar', [TeacherProfileController::class, 'updateAvatar'])->name('update-avatar');
    });
    
    // Paramètres Live
    Route::prefix('live-settings')->name('live-settings.')->group(function () {
        Route::get('/', [TeacherCourseController::class, 'liveSettings'])->name('index');
        Route::put('/', [TeacherCourseController::class, 'updateLiveSettings'])->name('update');
    });
    
    // Objectifs pédagogiques
    Route::prefix('objectives')->name('objectives.')->group(function () {
        Route::get('/', [TeacherCourseController::class, 'objectives'])->name('index');
        Route::post('/', [TeacherCourseController::class, 'storeObjective'])->name('store');
        Route::put('/{objective}', [TeacherCourseController::class, 'updateObjective'])->name('update');
        Route::delete('/{objective}', [TeacherCourseController::class, 'destroyObjective'])->name('destroy');
    });
    
});

/*
|--------------------------------------------------------------------------
| Routes Espace Administrateur
|--------------------------------------------------------------------------
|
| Middleware: auth, verified, role:admin
|
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Gestion des utilisateurs
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/create', [AdminUserController::class, 'create'])->name('create');
        Route::post('/', [AdminUserController::class, 'store'])->name('store');
        Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
        
        // Actions spécifiques
        Route::post('/{user}/change-status', [AdminUserController::class, 'changeStatus'])->name('change-status');
        Route::post('/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{user}/impersonate', [AdminUserController::class, 'impersonate'])->name('impersonate');
        Route::get('/export', [AdminUserController::class, 'export'])->name('export');
    });
    
    // Gestion des formations
    Route::prefix('formations')->name('formations.')->group(function () {
        Route::get('/', [AdminFormationController::class, 'index'])->name('index');
        Route::get('/create', [AdminFormationController::class, 'create'])->name('create');
        Route::post('/', [AdminFormationController::class, 'store'])->name('store');
        Route::get('/{formation}', [AdminFormationController::class, 'show'])->name('show');
        Route::get('/{formation}/edit', [AdminFormationController::class, 'edit'])->name('edit');
        Route::put('/{formation}', [AdminFormationController::class, 'update'])->name('update');
        Route::delete('/{formation}', [AdminFormationController::class, 'destroy'])->name('destroy');
        
        // Actions spécifiques
        Route::post('/{formation}/publish', [AdminFormationController::class, 'publish'])->name('publish');
        Route::post('/{formation}/unpublish', [AdminFormationController::class, 'unpublish'])->name('unpublish');
        Route::post('/{formation}/duplicate', [AdminFormationController::class, 'duplicate'])->name('duplicate');
    });
    
    // Gestion des classes
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [AdminClassController::class, 'index'])->name('index');
        Route::get('/create', [AdminClassController::class, 'create'])->name('create');
        Route::post('/', [AdminClassController::class, 'store'])->name('store');
        Route::get('/{class}', [AdminClassController::class, 'show'])->name('show');
        Route::get('/{class}/edit', [AdminClassController::class, 'edit'])->name('edit');
        Route::put('/{class}', [AdminClassController::class, 'update'])->name('update');
        Route::delete('/{class}', [AdminClassController::class, 'destroy'])->name('destroy');
        
        // Actions spécifiques
        Route::get('/{class}/students', [AdminClassController::class, 'students'])->name('students');
        Route::post('/{class}/assign-students', [AdminClassController::class, 'assignStudents'])->name('assign-students');
        Route::post('/{class}/auto-assign', [AdminClassController::class, 'autoAssignStudents'])->name('auto-assign');
        Route::post('/{class}/assign-teacher', [AdminClassController::class, 'assignTeacher'])->name('assign-teacher');
        Route::get('/{class}/schedule', [AdminClassController::class, 'schedule'])->name('schedule');
        Route::post('/{class}/schedule', [AdminClassController::class, 'updateSchedule'])->name('update-schedule');
    });
    
    // Gestion des enseignants
    Route::prefix('teachers')->name('teachers.')->group(function () {
        Route::get('/', [AdminTeacherController::class, 'index'])->name('index');
        Route::get('/pending', [AdminTeacherController::class, 'pending'])->name('pending');
        Route::get('/{teacher}', [AdminTeacherController::class, 'show'])->name('show');
        
        // Validation candidatures
        Route::post('/{teacher}/approve', [AdminTeacherController::class, 'approve'])->name('approve');
        Route::post('/{teacher}/reject', [AdminTeacherController::class, 'reject'])->name('reject');
        Route::post('/{teacher}/suspend', [AdminTeacherController::class, 'suspend'])->name('suspend');
        
        // Assignments
        Route::get('/{teacher}/classes', [AdminTeacherController::class, 'classes'])->name('classes');
        Route::post('/{teacher}/assign-class', [AdminTeacherController::class, 'assignClass'])->name('assign-class');
    });
    
    // Gestion des étudiants
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [AdminStudentController::class, 'index'])->name('index');
        Route::get('/{student}', [AdminStudentController::class, 'show'])->name('show');
        Route::get('/{student}/enrollments', [AdminStudentController::class, 'enrollments'])->name('enrollments');
        Route::post('/{student}/enroll', [AdminStudentController::class, 'enroll'])->name('enroll');
        Route::get('/{student}/grades', [AdminStudentController::class, 'grades'])->name('grades');
        Route::get('/{student}/export', [AdminStudentController::class, 'exportData'])->name('export-data');
    });
    
    // Gestion financière
    Route::prefix('finances')->name('finances.')->group(function () {
        Route::get('/', [AdminFinanceController::class, 'index'])->name('index');
        Route::get('/payments', [AdminFinanceController::class, 'payments'])->name('payments');
        Route::get('/payment/{payment}', [AdminFinanceController::class, 'showPayment'])->name('show-payment');
        Route::post('/payment/{payment}/refund', [AdminFinanceController::class, 'refund'])->name('refund');
        
        // Rapports
        Route::get('/reports', [AdminFinanceController::class, 'reports'])->name('reports');
        Route::get('/reports/revenue', [AdminFinanceController::class, 'revenueReport'])->name('revenue-report');
        Route::get('/reports/enrollments', [AdminFinanceController::class, 'enrollmentsReport'])->name('enrollments-report');
        Route::get('/export-transactions', [AdminFinanceController::class, 'exportTransactions'])->name('export-transactions');
    });
    
    // Système / Monitoring
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/', [AdminSystemController::class, 'index'])->name('index');
        Route::get('/logs', [AdminSystemController::class, 'logs'])->name('logs');
        Route::get('/logs/{file}', [AdminSystemController::class, 'viewLog'])->name('view-log');
        
        // Actions système
        Route::post('/cache/clear', [AdminSystemController::class, 'clearCache'])->name('clear-cache');
        Route::post('/optimize', [AdminSystemController::class, 'optimize'])->name('optimize');
        Route::get('/backups', [AdminSystemController::class, 'backups'])->name('backups');
        Route::post('/backup/create', [AdminSystemController::class, 'createBackup'])->name('create-backup');
        Route::get('/backup/{backup}/download', [AdminSystemController::class, 'downloadBackup'])->name('download-backup');
        Route::delete('/backup/{backup}', [AdminSystemController::class, 'deleteBackup'])->name('delete-backup');
        
        // Maintenance
        Route::get('/maintenance', [AdminSystemController::class, 'maintenance'])->name('maintenance');
        Route::post('/maintenance/enable', [AdminSystemController::class, 'enableMaintenance'])->name('enable-maintenance');
        Route::post('/maintenance/disable', [AdminSystemController::class, 'disableMaintenance'])->name('disable-maintenance');
    });
    
    // Paramètres
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
        
        // Paramètres généraux
        Route::get('/general', [AdminSettingsController::class, 'general'])->name('general');
        Route::post('/general', [AdminSettingsController::class, 'updateGeneral'])->name('update-general');
        
        // Paramètres email
        Route::get('/email', [AdminSettingsController::class, 'email'])->name('email');
        Route::post('/email', [AdminSettingsController::class, 'updateEmail'])->name('update-email');
        Route::post('/email/test', [AdminSettingsController::class, 'testEmail'])->name('test-email');
        
        // Intégrations
        Route::get('/integrations', [AdminSettingsController::class, 'integrations'])->name('integrations');
        Route::post('/integrations', [AdminSettingsController::class, 'updateIntegrations'])->name('update-integrations');
        Route::post('/integrations/zoom/test', [AdminSettingsController::class, 'testZoom'])->name('test-zoom');
        Route::post('/integrations/stripe/test', [AdminSettingsController::class, 'testStripe'])->name('test-stripe');
        
        // Sécurité
        Route::get('/security', [AdminSettingsController::class, 'security'])->name('security');
        Route::post('/security', [AdminSettingsController::class, 'updateSecurity'])->name('update-security');
        
        // Notifications
        Route::get('/notifications', [AdminSettingsController::class, 'notifications'])->name('notifications');
        Route::post('/notifications', [AdminSettingsController::class, 'updateNotifications'])->name('update-notifications');
        
        // Inscriptions
        Route::get('/enrollments', [AdminSettingsController::class, 'enrollments'])->name('enrollments');
        Route::post('/enrollments', [AdminSettingsController::class, 'updateEnrollments'])->name('update-enrollments');
        
        // Paiements
        Route::get('/payments', [AdminSettingsController::class, 'payments'])->name('payments');
        Route::post('/payments', [AdminSettingsController::class, 'updatePayments'])->name('update-payments');
    });
    
    // Rapports globaux
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'reports'])->name('index');
        Route::get('/users', [AdminDashboardController::class, 'usersReport'])->name('users');
        Route::get('/courses', [AdminDashboardController::class, 'coursesReport'])->name('courses');
        Route::get('/attendance', [AdminDashboardController::class, 'attendanceReport'])->name('attendance');
    });
    
});

/*
|--------------------------------------------------------------------------
| Routes de Stop Impersonation
|--------------------------------------------------------------------------
*/

Route::get('/stop-impersonating', [AdminUserController::class, 'stopImpersonating'])
    ->name('stop-impersonating')
    ->middleware('auth');