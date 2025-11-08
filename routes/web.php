<?php

use Illuminate\Support\Facades\Route;

// Controllers de base
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TeacherController;

// Controllers d'authentification
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\SocialController;

// Controllers communs (tous rôles)
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SupportController;

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

// Controllers Enseignant
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Teacher\ScheduleController as TeacherScheduleController;
use App\Http\Controllers\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Teacher\ResourceController as TeacherResourceController;
use App\Http\Controllers\Teacher\GradeController as TeacherGradeController;
use App\Http\Controllers\Teacher\AnalyticsController as TeacherAnalyticsController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Teacher\MessageController as TeacherMessageController;
use App\Http\Controllers\Teacher\EarningController as TeacherEarningController;

// Controllers Administrateur
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\FormationController as AdminFormationController;
use App\Http\Controllers\Admin\ClassController as AdminClassController;
use App\Http\Controllers\Admin\TeacherController as AdminTeacherController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\RevenueController as AdminRevenueController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ActivityController as AdminActivityController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SupportController as AdminSupportController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\Admin\AlertController as AdminAlertController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SystemController as AdminSystemController;

/*
|--------------------------------------------------------------------------
| Routes Publiques
|--------------------------------------------------------------------------
*/

// Page d'accueil
Route::get('/', [HomeController::class, 'index'])->name('home');

// Pages d'information
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/teachers', [PageController::class, 'teachers'])->name('teachers');
Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/blog', [PageController::class, 'blog'])->name('blog');

// Formations publiques
Route::get('/formations', [FormationController::class, 'index'])->name('formations.index');
Route::get('/formations/{slug}', [FormationController::class, 'show'])->name('formations.show');

// Pages légales
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/legal', [PageController::class, 'legal'])->name('legal');

/*
|--------------------------------------------------------------------------
| Routes d'Authentification
|--------------------------------------------------------------------------
*/

// Connexion
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Inscription
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Mot de passe oublié
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Vérification email
Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

// OAuth Social Login
Route::get('/auth/{provider}', [SocialController::class, 'redirect'])->name('auth.social');
Route::get('/auth/{provider}/callback', [SocialController::class, 'callback']);

/*
|--------------------------------------------------------------------------
| Routes Authentifiées - Communes à tous les rôles
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // Profil utilisateur
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::put('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences.update');
    Route::post('/profile/2fa/enable', [ProfileController::class, 'enable2FA'])->name('profile.2fa.enable');
    Route::delete('/profile/2fa/disable', [ProfileController::class, 'disable2FA'])->name('profile.2fa.disable');
    Route::delete('/profile', [ProfileController::class, 'delete'])->name('profile.delete');
    
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
    
    // Support (accessible à tous les rôles)
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('index');
        Route::get('/create', [SupportController::class, 'create'])->name('create');
        Route::post('/', [SupportController::class, 'store'])->name('store');
        Route::get('/{ticket}', [SupportController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [SupportController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/close', [SupportController::class, 'close'])->name('close');
    });
    
});

/*
|--------------------------------------------------------------------------
| Routes Étudiant
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    
    // Formations/Cours
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', [StudentCourseController::class, 'index'])->name('index');
        Route::get('/{enrollment}', [StudentCourseController::class, 'show'])->name('show');
        Route::get('/{enrollment}/continue', [StudentCourseController::class, 'continue'])->name('continue');
        Route::get('/{enrollment}/resume', [StudentCourseController::class, 'resume'])->name('resume');
        Route::post('/{enrollment}/complete-lesson', [StudentCourseController::class, 'completeLesson'])->name('complete-lesson');
    });
    
    // Planning/Calendrier
    Route::prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/', [StudentScheduleController::class, 'index'])->name('index');
        Route::get('/ical', [StudentScheduleController::class, 'ical'])->name('ical');
    });
    
    // Progression
    Route::prefix('progress')->name('progress.')->group(function () {
        Route::get('/', [StudentProgressController::class, 'index'])->name('index');
        Route::get('/{enrollment}', [StudentProgressController::class, 'show'])->name('show');
    });
    
    // Devoirs
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
    });
    
    // Enregistrements de cours
    Route::prefix('recordings')->name('recordings.')->group(function () {
        Route::get('/', [StudentRecordingController::class, 'index'])->name('index');
        Route::get('/{recording}', [StudentRecordingController::class, 'show'])->name('show');
    });
    
    // Notes
    Route::prefix('grades')->name('grades.')->group(function () {
        Route::get('/', [StudentGradeController::class, 'index'])->name('index');
        Route::get('/{enrollment}', [StudentGradeController::class, 'show'])->name('show');
    });
    
    // Certificats
    Route::prefix('certificates')->name('certificates.')->group(function () {
        Route::get('/', [StudentCertificateController::class, 'index'])->name('index');
        Route::get('/{enrollment}/download', [StudentCertificateController::class, 'download'])->name('download');
        Route::get('/{certificate}/verify', [StudentCertificateController::class, 'verify'])->name('verify');
    });
    
    // Communauté/Forum
    Route::prefix('community')->name('community.')->group(function () {
        Route::get('/', [StudentCommunityController::class, 'index'])->name('index');
        Route::get('/{topic}', [StudentCommunityController::class, 'show'])->name('show');
        Route::post('/{topic}/reply', [StudentCommunityController::class, 'reply'])->name('reply');
    });
    
});

/*
|--------------------------------------------------------------------------
| Routes Inscription Formation (authentifiées)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/formations/{slug}/enroll', [EnrollmentController::class, 'create'])->name('formations.enroll');
    Route::post('/formations/{slug}/enroll', [EnrollmentController::class, 'store'])->name('formations.enroll.store');
    Route::post('/enrollments/{enrollment}/cancel', [EnrollmentController::class, 'cancel'])->name('enrollments.cancel');
});

/*
|--------------------------------------------------------------------------
| Routes Enseignant
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    
    // Gestion des cours/formations
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', [TeacherCourseController::class, 'index'])->name('index');
        Route::get('/create', [TeacherCourseController::class, 'create'])->name('create');
        Route::post('/', [TeacherCourseController::class, 'store'])->name('store');
        Route::get('/{course}', [TeacherCourseController::class, 'show'])->name('show');
        Route::get('/{course}/edit', [TeacherCourseController::class, 'edit'])->name('edit');
        Route::put('/{course}', [TeacherCourseController::class, 'update'])->name('update');
        Route::delete('/{course}', [TeacherCourseController::class, 'destroy'])->name('destroy');
        Route::get('/{course}/students', [TeacherCourseController::class, 'students'])->name('students');
        Route::post('/{course}/publish', [TeacherCourseController::class, 'publish'])->name('publish');
        Route::post('/{course}/unpublish', [TeacherCourseController::class, 'unpublish'])->name('unpublish');
    });
    
    // Planning
    Route::prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/', [TeacherScheduleController::class, 'index'])->name('index');
        Route::get('/create', [TeacherScheduleController::class, 'create'])->name('create');
        Route::post('/', [TeacherScheduleController::class, 'store'])->name('store');
        Route::get('/{class}/edit', [TeacherScheduleController::class, 'edit'])->name('edit');
        Route::put('/{class}', [TeacherScheduleController::class, 'update'])->name('update');
        Route::delete('/{class}', [TeacherScheduleController::class, 'destroy'])->name('destroy');
    });
    
    // Classes en direct
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [TeacherClassController::class, 'index'])->name('index');
        Route::get('/{class}', [TeacherClassController::class, 'show'])->name('show');
        Route::post('/{class}/start', [TeacherClassController::class, 'start'])->name('start');
        Route::post('/{class}/end', [TeacherClassController::class, 'end'])->name('end');
        Route::post('/{class}/attendance', [TeacherClassController::class, 'recordAttendance'])->name('attendance');
    });
    
    // Ressources
    Route::prefix('resources')->name('resources.')->group(function () {
        Route::get('/', [TeacherResourceController::class, 'index'])->name('index');
        Route::get('/upload', [TeacherResourceController::class, 'create'])->name('upload');
        Route::post('/', [TeacherResourceController::class, 'store'])->name('store');
        Route::delete('/{resource}', [TeacherResourceController::class, 'destroy'])->name('destroy');
    });
    
    // Notation
    Route::prefix('grades')->name('grades.')->group(function () {
        Route::get('/', [TeacherGradeController::class, 'index'])->name('index');
        Route::get('/{course}', [TeacherGradeController::class, 'show'])->name('show');
        Route::post('/{enrollment}/grade', [TeacherGradeController::class, 'store'])->name('store');
        Route::put('/{grade}', [TeacherGradeController::class, 'update'])->name('update');
    });
    
    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [TeacherAnalyticsController::class, 'index'])->name('index');
        Route::get('/{course}', [TeacherAnalyticsController::class, 'show'])->name('show');
    });
    
    // Gestion étudiants
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [TeacherStudentController::class, 'index'])->name('index');
        Route::get('/{student}', [TeacherStudentController::class, 'show'])->name('show');
    });
    
    // Messages
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [TeacherMessageController::class, 'index'])->name('index');
        Route::get('/{message}', [TeacherMessageController::class, 'show'])->name('show');
    });
    
    // Revenus
    Route::prefix('earnings')->name('earnings.')->group(function () {
        Route::get('/', [TeacherEarningController::class, 'index'])->name('index');
        Route::get('/history', [TeacherEarningController::class, 'history'])->name('history');
        Route::get('/export', [TeacherEarningController::class, 'export'])->name('export');
    });
    
    // Profil enseignant
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    
});

/*
|--------------------------------------------------------------------------
| Routes Administrateur
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Gestion utilisateurs
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/create', [AdminUserController::class, 'create'])->name('create');
        Route::post('/', [AdminUserController::class, 'store'])->name('store');
        Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/impersonate', [AdminUserController::class, 'impersonate'])->name('impersonate');
        Route::post('/{user}/ban', [AdminUserController::class, 'ban'])->name('ban');
        Route::post('/{user}/unban', [AdminUserController::class, 'unban'])->name('unban');
    });
    
    // Gestion formations
    Route::prefix('formations')->name('formations.')->group(function () {
        Route::get('/', [AdminFormationController::class, 'index'])->name('index');
        Route::get('/pending', [AdminFormationController::class, 'pending'])->name('pending');
        Route::get('/analytics', [AdminFormationController::class, 'analytics'])->name('analytics');
        Route::get('/{formation}', [AdminFormationController::class, 'show'])->name('show');
        Route::get('/{formation}/edit', [AdminFormationController::class, 'edit'])->name('edit');
        Route::put('/{formation}', [AdminFormationController::class, 'update'])->name('update');
        Route::delete('/{formation}', [AdminFormationController::class, 'destroy'])->name('destroy');
        Route::post('/{formation}/approve', [AdminFormationController::class, 'approve'])->name('approve');
        Route::post('/{formation}/reject', [AdminFormationController::class, 'reject'])->name('reject');
    });
    
    // Gestion classes
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [AdminClassController::class, 'index'])->name('index');
        Route::get('/create', [AdminClassController::class, 'create'])->name('create');
        Route::post('/', [AdminClassController::class, 'store'])->name('store');
        Route::get('/{class}', [AdminClassController::class, 'show'])->name('show');
        Route::get('/{class}/edit', [AdminClassController::class, 'edit'])->name('edit');
        Route::put('/{class}', [AdminClassController::class, 'update'])->name('update');
        Route::delete('/{class}', [AdminClassController::class, 'destroy'])->name('destroy');
        Route::get('/{class}/students', [AdminClassController::class, 'students'])->name('students');
        Route::post('/{class}/assign-students', [AdminClassController::class, 'assignStudents'])->name('assign-students');
    });
    
    // Gestion enseignants
    Route::prefix('teachers')->name('teachers.')->group(function () {
        Route::get('/', [AdminTeacherController::class, 'index'])->name('index');
        Route::get('/pending', [AdminTeacherController::class, 'pending'])->name('pending');
        Route::get('/{teacher}', [AdminTeacherController::class, 'show'])->name('show');
        Route::post('/{teacher}/approve', [AdminTeacherController::class, 'approve'])->name('approve');
        Route::post('/{teacher}/reject', [AdminTeacherController::class, 'reject'])->name('reject');
    });
    
    // Gestion étudiants
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [AdminStudentController::class, 'index'])->name('index');
        Route::get('/{student}', [AdminStudentController::class, 'show'])->name('show');
        Route::get('/{student}/enrollments', [AdminStudentController::class, 'enrollments'])->name('enrollments');
    });
    
    // Gestion paiements
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [AdminPaymentController::class, 'index'])->name('index');
        Route::get('/{payment}', [AdminPaymentController::class, 'show'])->name('show');
        Route::post('/{payment}/refund', [AdminPaymentController::class, 'refund'])->name('refund');
    });
    
    // Revenus
    Route::prefix('revenue')->name('revenue.')->group(function () {
        Route::get('/', [AdminRevenueController::class, 'index'])->name('index');
        Route::get('/export', [AdminRevenueController::class, 'export'])->name('export');
    });
    
    // Avis/Reviews
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [AdminReviewController::class, 'index'])->name('index');
        Route::get('/flagged', [AdminReviewController::class, 'flagged'])->name('flagged');
        Route::post('/{review}/approve', [AdminReviewController::class, 'approve'])->name('approve');
        Route::delete('/{review}', [AdminReviewController::class, 'destroy'])->name('destroy');
    });
    
    // Activités
    Route::prefix('activity')->name('activity.')->group(function () {
        Route::get('/', [AdminActivityController::class, 'index'])->name('index');
        Route::get('/export', [AdminActivityController::class, 'export'])->name('export');
    });
    
    // Rapports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AdminReportController::class, 'index'])->name('index');
        Route::get('/revenue', [AdminReportController::class, 'revenue'])->name('revenue');
        Route::get('/users', [AdminReportController::class, 'users'])->name('users');
        Route::get('/courses', [AdminReportController::class, 'courses'])->name('courses');
        Route::get('/attendance', [AdminReportController::class, 'attendance'])->name('attendance');
    });
    
    // Support
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [AdminSupportController::class, 'index'])->name('index');
        Route::get('/{ticket}', [AdminSupportController::class, 'show'])->name('show');
        Route::post('/{ticket}/assign', [AdminSupportController::class, 'assign'])->name('assign');
        Route::post('/{ticket}/resolve', [AdminSupportController::class, 'resolve'])->name('resolve');
    });
    
    // Demandes en attente
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/{request}', [AdminRequestController::class, 'show'])->name('show');
        Route::post('/{request}/approve', [AdminRequestController::class, 'approve'])->name('approve');
        Route::post('/{request}/reject', [AdminRequestController::class, 'reject'])->name('reject');
    });
    
    // Alertes système
    Route::prefix('alerts')->name('alerts.')->group(function () {
        Route::post('/send', [AdminAlertController::class, 'send'])->name('send');
    });
    
    // Paramètres
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [AdminSettingController::class, 'index'])->name('index');
        Route::put('/', [AdminSettingController::class, 'update'])->name('update');
        Route::get('/general', [AdminSettingController::class, 'general'])->name('general');
        Route::get('/email', [AdminSettingController::class, 'email'])->name('email');
        Route::get('/integrations', [AdminSettingController::class, 'integrations'])->name('integrations');
        Route::get('/security', [AdminSettingController::class, 'security'])->name('security');
    });
    
    // Système
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/', [AdminSystemController::class, 'index'])->name('index');
        Route::get('/logs', [AdminSystemController::class, 'logs'])->name('logs');
        Route::post('/cache/clear', [AdminSystemController::class, 'clearCache'])->name('clear-cache');
        Route::post('/optimize', [AdminSystemController::class, 'optimize'])->name('optimize');
        Route::get('/backups', [AdminSystemController::class, 'backups'])->name('backups');
        Route::post('/backup/create', [AdminSystemController::class, 'createBackup'])->name('create-backup');
    });
    
});

// Stop Impersonation (admin)
Route::get('/stop-impersonating', [AdminUserController::class, 'stopImpersonating'])
    ->name('stop-impersonating')
    ->middleware('auth');