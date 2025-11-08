<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseApiController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes API pour interactions AJAX et applications mobiles
| Authentification via Laravel Sanctum
|
*/

/*
|--------------------------------------------------------------------------
| Routes API Publiques
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'version' => config('app.version', '1.0.0'),
    ]);
});

// Formations publiques (catalogue)
Route::get('/formations', [CourseApiController::class, 'publicFormations']);
Route::get('/formations/{formation:slug}', [CourseApiController::class, 'publicFormationDetails']);

/*
|--------------------------------------------------------------------------
| Routes API Authentifiées
|--------------------------------------------------------------------------
|
| Middleware: auth:sanctum
|
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    /*
    |--------------------------------------------------------------------------
    | API Cours / Courses
    |--------------------------------------------------------------------------
    */
    Route::prefix('courses')->name('api.courses.')->group(function () {
        
        // Liste des cours
        Route::get('/', [CourseApiController::class, 'index'])->name('index');
        Route::get('/upcoming', [CourseApiController::class, 'upcoming'])->name('upcoming');
        Route::get('/today', [CourseApiController::class, 'today'])->name('today');
        Route::get('/this-week', [CourseApiController::class, 'thisWeek'])->name('this-week');
        
        // Détails d'un cours
        Route::get('/{course}', [CourseApiController::class, 'show'])->name('show');
        
        // Actions sur un cours
        Route::post('/{course}/join', [CourseApiController::class, 'join'])->name('join');
        Route::post('/{course}/leave', [CourseApiController::class, 'leave'])->name('leave');
        Route::get('/{course}/status', [CourseApiController::class, 'status'])->name('status');
        Route::get('/{course}/participants', [CourseApiController::class, 'participants'])->name('participants');
        
        // Cours live
        Route::get('/{course}/live-status', [CourseApiController::class, 'liveStatus'])->name('live-status');
        Route::post('/{course}/heartbeat', [CourseApiController::class, 'heartbeat'])->name('heartbeat');
        
        // Ressources du cours
        Route::get('/{course}/resources', [CourseApiController::class, 'resources'])->name('resources');
        Route::get('/{course}/assignments', [CourseApiController::class, 'assignments'])->name('assignments');
        
    });
    
    /*
    |--------------------------------------------------------------------------
    | API Notifications
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('api.notifications.')->group(function () {
        
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        
        // Actions sur les notifications
        Route::post('/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
        
    });
    
    /*
    |--------------------------------------------------------------------------
    | API Chat / Messages en temps réel
    |--------------------------------------------------------------------------
    */
    Route::prefix('chat')->name('api.chat.')->group(function () {
        
        // Conversations
        Route::get('/conversations', [ChatController::class, 'conversations'])->name('conversations');
        Route::get('/conversation/{userId}', [ChatController::class, 'conversation'])->name('conversation');
        
        // Messages
        Route::get('/messages', [ChatController::class, 'messages'])->name('messages');
        Route::post('/send', [ChatController::class, 'send'])->name('send');
        Route::post('/typing', [ChatController::class, 'typing'])->name('typing');
        Route::post('/stop-typing', [ChatController::class, 'stopTyping'])->name('stop-typing');
        
        // Messages de groupe (cours live)
        Route::get('/course/{course}/messages', [ChatController::class, 'courseMessages'])->name('course-messages');
        Route::post('/course/{course}/send', [ChatController::class, 'sendCourseMessage'])->name('send-course-message');
        
    });
    
    /*
    |--------------------------------------------------------------------------
    | API Présence / Attendance
    |--------------------------------------------------------------------------
    */
    Route::prefix('attendance')->name('api.attendance.')->group(function () {
        
        Route::post('/course/{course}/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
        Route::post('/course/{course}/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
        Route::get('/course/{course}', [AttendanceController::class, 'courseAttendance'])->name('course');
        Route::get('/student/{student}', [AttendanceController::class, 'studentAttendance'])->name('student');
        
    });
    
    /*
    |--------------------------------------------------------------------------
    | API Planning / Schedule
    |--------------------------------------------------------------------------
    */
    Route::prefix('schedule')->name('api.schedule.')->group(function () {
        
        Route::get('/today', [ScheduleController::class, 'today'])->name('today');
        Route::get('/this-week', [ScheduleController::class, 'thisWeek'])->name('this-week');
        Route::get('/month/{year}/{month}', [ScheduleController::class, 'month'])->name('month');
        Route::get('/upcoming', [ScheduleController::class, 'upcoming'])->name('upcoming');
        
    });
    
    /*
    |--------------------------------------------------------------------------
    | API Ressources
    |--------------------------------------------------------------------------
    */
    Route::prefix('resources')->name('api.resources.')->group(function () {
        
        Route::get('/', [ResourceController::class, 'index'])->name('index');
        Route::get('/{resource}', [ResourceController::class, 'show'])->name('show');
        Route::get('/{resource}/download-url', [ResourceController::class, 'downloadUrl'])->name('download-url');
        Route::post('/{resource}/track-download', [ResourceController::class, 'trackDownload'])->name('track-download');
        
    });
    
    /*
    |--------------------------------------------------------------------------
    | API Notes / Grades
    |--------------------------------------------------------------------------
    */
    Route::prefix('grades')->name('api.grades.')->group(function () {
        
        Route::get('/student/{student}', [GradeController::class, 'studentGrades'])->name('student');
        Route::get('/subject/{subject}', [GradeController::class, 'subjectGrades'])->name('subject');
        Route::get('/class/{class}', [GradeController::class, 'classGrades'])->name('class');
        Route::get('/statistics', [GradeController::class, 'statistics'])->name('statistics');
        
    });
    
    /*
    |--------------------------------------------------------------------------
    | API Dashboard
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->name('api.dashboard.')->group(function () {
        
        // Dashboard étudiant
        Route::get('/student/stats', [DashboardController::class, 'studentStats'])->name('student-stats');
        Route::get('/student/upcoming-courses', [DashboardController::class, 'studentUpcomingCourses'])->name('student-upcoming');
        Route::get('/student/recent-grades', [DashboardController::class, 'studentRecentGrades'])->name('student-grades');
        Route::get('/student/progress', [DashboardController::class, 'studentProgress'])->name('student-progress');
        
        // Dashboard enseignant
        Route::get('/teacher/stats', [DashboardController::class, 'teacherStats'])->name('teacher-stats');
        Route::get('/teacher/today-courses', [DashboardController::class, 'teacherTodayCourses'])->name('teacher-today');
        Route::get('/teacher/pending-grades', [DashboardController::class, 'teacherPendingGrades'])->name('teacher-pending');
        
        // Dashboard admin
        Route::get('/admin/stats', [DashboardController::class, 'adminStats'])->name('admin-stats');
        Route::get('/admin/recent-activity', [DashboardController::class, 'adminRecentActivity'])->name('admin-activity');
        Route::get('/admin/alerts', [DashboardController::class, 'adminAlerts'])->name('admin-alerts');
        
    });
    
    /*
    |--------------------------------------------------------------------------
    | API Recherche
    |--------------------------------------------------------------------------
    */
    Route::prefix('search')->name('api.search.')->group(function () {
        
        Route::get('/users', [DashboardController::class, 'searchUsers'])->name('users');
        Route::get('/courses', [CourseApiController::class, 'search'])->name('courses');
        Route::get('/resources', [ResourceController::class, 'search'])->name('resources');
        Route::get('/global', [DashboardController::class, 'globalSearch'])->name('global');
        
    });
    
    /*
    |--------------------------------------------------------------------------
    | API Upload de fichiers
    |--------------------------------------------------------------------------
    */
    Route::prefix('upload')->name('api.upload.')->group(function () {
        
        Route::post('/avatar', [DashboardController::class, 'uploadAvatar'])->name('avatar');
        Route::post('/resource', [ResourceController::class, 'upload'])->name('resource');
        Route::post('/assignment-submission', [DashboardController::class, 'uploadAssignmentSubmission'])->name('assignment-submission');
        Route::post('/image', [DashboardController::class, 'uploadImage'])->name('image');
        
    });
    
    /*
    |--------------------------------------------------------------------------
    | API Statistiques temps réel
    |--------------------------------------------------------------------------
    */
    Route::prefix('stats')->name('api.stats.')->group(function () {
        
        // Stats globales
        Route::get('/platform', [DashboardController::class, 'platformStats'])->name('platform');
        Route::get('/online-users', [DashboardController::class, 'onlineUsers'])->name('online-users');
        Route::get('/live-courses', [CourseApiController::class, 'liveCourses'])->name('live-courses');
        
        // Stats par période
        Route::get('/period/{period}', [DashboardController::class, 'periodStats'])->name('period');
        
    });
    
});

/*
|--------------------------------------------------------------------------
| Routes API spécifiques par rôle
|--------------------------------------------------------------------------
*/

// API Enseignant
Route::prefix('teacher')->name('api.teacher.')->middleware(['auth:sanctum', 'role:teacher'])->group(function () {
    
    // Gestion cours
    Route::post('/courses/{course}/start', [CourseApiController::class, 'startCourse'])->name('start-course');
    Route::post('/courses/{course}/end', [CourseApiController::class, 'endCourse'])->name('end-course');
    Route::post('/courses/{course}/recording/start', [CourseApiController::class, 'startRecording'])->name('start-recording');
    Route::post('/courses/{course}/recording/stop', [CourseApiController::class, 'stopRecording'])->name('stop-recording');
    
    // Contrôles live
    Route::post('/courses/{course}/participant/{participant}/mute', [CourseApiController::class, 'muteParticipant'])->name('mute-participant');
    Route::post('/courses/{course}/participant/{participant}/unmute', [CourseApiController::class, 'unmuteParticipant'])->name('unmute-participant');
    Route::post('/courses/{course}/participant/{participant}/remove', [CourseApiController::class, 'removeParticipant'])->name('remove-participant');
    
    // Quick grade
    Route::post('/quick-grade', [GradeController::class, 'quickGrade'])->name('quick-grade');
    
    // Analytics en temps réel
    Route::get('/analytics/engagement/{class}', [DashboardController::class, 'classEngagement'])->name('class-engagement');
    
});

// API Admin
Route::prefix('admin')->name('api.admin.')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    
    // Actions bulk
    Route::post('/users/bulk-action', [DashboardController::class, 'bulkUserAction'])->name('bulk-user-action');
    Route::post('/courses/bulk-action', [CourseApiController::class, 'bulkCourseAction'])->name('bulk-course-action');
    
    // Auto-assignation
    Route::post('/classes/{class}/auto-assign', [DashboardController::class, 'autoAssignStudents'])->name('auto-assign-students');
    
    // Monitoring
    Route::get('/system/health', [DashboardController::class, 'systemHealth'])->name('system-health');
    Route::get('/system/performance', [DashboardController::class, 'systemPerformance'])->name('system-performance');
    Route::get('/system/errors', [DashboardController::class, 'systemErrors'])->name('system-errors');
    
});

/*
|--------------------------------------------------------------------------
| Webhooks
|--------------------------------------------------------------------------
*/

// Zoom webhooks
Route::post('/webhooks/zoom', [CourseApiController::class, 'zoomWebhook'])->name('webhooks.zoom');

// Stripe webhooks
Route::post('/webhooks/stripe', [DashboardController::class, 'stripeWebhook'])->name('webhooks.stripe');

/*
|--------------------------------------------------------------------------
| Routes de test (à retirer en production)
|--------------------------------------------------------------------------
*/

if (config('app.env') !== 'production') {
    Route::prefix('test')->name('api.test.')->group(function () {
        
        Route::get('/notification', function () {
            return response()->json([
                'message' => 'Test notification endpoint working',
                'timestamp' => now()->toIso8601String(),
            ]);
        });
        
        Route::get('/broadcast', function () {
            return response()->json([
                'message' => 'Test broadcast endpoint working',
                'timestamp' => now()->toIso8601String(),
            ]);
        });
        
    });
}