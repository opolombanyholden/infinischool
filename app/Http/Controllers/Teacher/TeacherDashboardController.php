<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\Grade;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * TeacherDashboardController
 * 
 * Gère le tableau de bord principal de l'enseignant
 * Affiche les KPIs, statistiques et vue d'ensemble des activités
 * 
 * @package App\Http\Controllers\Teacher
 */
class TeacherDashboardController extends Controller
{
    /**
     * Affiche le tableau de bord de l'enseignant
     * 
     * @return View
     */
    public function index(): View
    {
        $teacher = Auth::user();
        
        // KPIs principaux
        $kpis = $this->getTeacherKPIs($teacher->id);
        
        // Cours à venir (prochaines 24h)
        $upcomingCourses = $this->getUpcomingCourses($teacher->id);
        
        // Cours en direct actuellement
        $liveCourses = $this->getLiveCourses($teacher->id);
        
        // Classes assignées
        $classes = $this->getTeacherClasses($teacher->id);
        
        // Statistiques récentes (7 derniers jours)
        $recentStats = $this->getRecentStatistics($teacher->id);
        
        // Notifications et alertes
        $notifications = $this->getTeacherNotifications($teacher->id);
        
        // Tâches en attente
        $pendingTasks = $this->getPendingTasks($teacher->id);
        
        return view('teacher.dashboard', compact(
            'teacher',
            'kpis',
            'upcomingCourses',
            'liveCourses',
            'classes',
            'recentStats',
            'notifications',
            'pendingTasks'
        ));
    }
    
    /**
     * Récupère les KPIs de l'enseignant
     * 
     * @param int $teacherId
     * @return array
     */
    private function getTeacherKPIs(int $teacherId): array
    {
        // Nombre total d'étudiants
        $totalStudents = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->where('courses.teacher_id', $teacherId)
            ->distinct('enrollments.user_id')
            ->count('enrollments.user_id');
        
        // Nombre de cours dispensés ce mois
        $coursesThisMonth = Course::where('teacher_id', $teacherId)
            ->where('status', 'completed')
            ->whereMonth('scheduled_at', Carbon::now()->month)
            ->count();
        
        // Note moyenne (rating)
        $averageRating = DB::table('course_ratings')
            ->join('courses', 'course_ratings.course_id', '=', 'courses.id')
            ->where('courses.teacher_id', $teacherId)
            ->avg('course_ratings.rating') ?? 0;
        
        // Taux de présence moyen
        $attendanceRate = DB::table('attendances')
            ->join('courses', 'attendances.course_id', '=', 'courses.id')
            ->where('courses.teacher_id', $teacherId)
            ->where('attendances.status', 'present')
            ->whereMonth('attendances.created_at', Carbon::now()->month)
            ->count() / max(1, $totalStudents * $coursesThisMonth) * 100;
        
        // Heures enseignées ce mois
        $hoursThisMonth = Course::where('teacher_id', $teacherId)
            ->where('status', 'completed')
            ->whereMonth('scheduled_at', Carbon::now()->month)
            ->sum('duration') / 60; // Conversion minutes en heures
        
        // Nombre de classes actives
        $activeClasses = DB::table('class_teacher')
            ->where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->count();
        
        return [
            'total_students' => $totalStudents,
            'courses_this_month' => $coursesThisMonth,
            'average_rating' => round($averageRating, 1),
            'attendance_rate' => round($attendanceRate, 1),
            'hours_this_month' => round($hoursThisMonth, 1),
            'active_classes' => $activeClasses,
            'pending_grades' => $this->getPendingGradesCount($teacherId),
            'unread_messages' => $this->getUnreadMessagesCount($teacherId),
        ];
    }
    
    /**
     * Récupère les cours à venir dans les prochaines 24h
     * 
     * @param int $teacherId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getUpcomingCourses(int $teacherId)
    {
        return Course::with(['class', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('status', 'scheduled')
            ->whereBetween('scheduled_at', [
                Carbon::now(),
                Carbon::now()->addDay()
            ])
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->get();
    }
    
    /**
     * Récupère les cours en direct actuellement
     * 
     * @param int $teacherId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getLiveCourses(int $teacherId)
    {
        return Course::with(['class', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('status', 'live')
            ->get();
    }
    
    /**
     * Récupère les classes de l'enseignant
     * 
     * @param int $teacherId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTeacherClasses(int $teacherId)
    {
        return ClassModel::whereHas('teachers', function($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->withCount('students')
            ->with(['formation', 'teachers'])
            ->get();
    }
    
    /**
     * Récupère les statistiques des 7 derniers jours
     * 
     * @param int $teacherId
     * @return array
     */
    private function getRecentStatistics(int $teacherId): array
    {
        $startDate = Carbon::now()->subDays(7);
        
        // Cours par jour
        $coursesPerDay = Course::where('teacher_id', $teacherId)
            ->where('scheduled_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(scheduled_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        // Taux de présence par jour
        $attendancePerDay = DB::table('attendances')
            ->join('courses', 'attendances.course_id', '=', 'courses.id')
            ->where('courses.teacher_id', $teacherId)
            ->where('attendances.created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(attendances.created_at) as date'),
                DB::raw('SUM(CASE WHEN attendances.status = "present" THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as rate')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        return [
            'courses_per_day' => $coursesPerDay,
            'attendance_per_day' => $attendancePerDay,
        ];
    }
    
    /**
     * Récupère les notifications de l'enseignant
     * 
     * @param int $teacherId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTeacherNotifications(int $teacherId)
    {
        return DB::table('notifications')
            ->where('user_id', $teacherId)
            ->where('read_at', null)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }
    
    /**
     * Récupère les tâches en attente
     * 
     * @param int $teacherId
     * @return array
     */
    private function getPendingTasks(int $teacherId): array
    {
        return [
            'pending_grades' => $this->getPendingGradesCount($teacherId),
            'scheduled_courses' => Course::where('teacher_id', $teacherId)
                ->where('status', 'scheduled')
                ->where('scheduled_at', '>', Carbon::now())
                ->count(),
            'unanswered_messages' => $this->getUnreadMessagesCount($teacherId),
            'pending_resources' => DB::table('course_resources')
                ->join('courses', 'course_resources.course_id', '=', 'courses.id')
                ->where('courses.teacher_id', $teacherId)
                ->where('course_resources.status', 'pending')
                ->count(),
        ];
    }
    
    /**
     * Compte le nombre de notes en attente
     * 
     * @param int $teacherId
     * @return int
     */
    private function getPendingGradesCount(int $teacherId): int
    {
        return DB::table('assignments')
            ->join('courses', 'assignments.course_id', '=', 'courses.id')
            ->leftJoin('grades', 'assignments.id', '=', 'grades.assignment_id')
            ->where('courses.teacher_id', $teacherId)
            ->whereNull('grades.id')
            ->where('assignments.due_date', '<', Carbon::now())
            ->count();
    }
    
    /**
     * Compte le nombre de messages non lus
     * 
     * @param int $teacherId
     * @return int
     */
    private function getUnreadMessagesCount(int $teacherId): int
    {
        return DB::table('messages')
            ->where('receiver_id', $teacherId)
            ->whereNull('read_at')
            ->count();
    }
    
    /**
     * Récupère les données pour les graphiques du dashboard (AJAX)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChartData(Request $request)
    {
        $teacher = Auth::user();
        $period = $request->input('period', '7days'); // 7days, 30days, 3months
        
        $startDate = match($period) {
            '7days' => Carbon::now()->subDays(7),
            '30days' => Carbon::now()->subDays(30),
            '3months' => Carbon::now()->subMonths(3),
            default => Carbon::now()->subDays(7),
        };
        
        $data = [
            'courses' => $this->getCoursesChartData($teacher->id, $startDate),
            'attendance' => $this->getAttendanceChartData($teacher->id, $startDate),
            'ratings' => $this->getRatingsChartData($teacher->id, $startDate),
        ];
        
        return response()->json($data);
    }
    
    /**
     * Données graphique des cours
     * 
     * @param int $teacherId
     * @param Carbon $startDate
     * @return array
     */
    private function getCoursesChartData(int $teacherId, Carbon $startDate): array
    {
        $courses = Course::where('teacher_id', $teacherId)
            ->where('scheduled_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(scheduled_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        return [
            'labels' => $courses->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray(),
            'datasets' => [
                [
                    'label' => 'Total',
                    'data' => $courses->pluck('total')->toArray(),
                    'backgroundColor' => '#800020',
                ],
                [
                    'label' => 'Complétés',
                    'data' => $courses->pluck('completed')->toArray(),
                    'backgroundColor' => '#28a745',
                ],
                [
                    'label' => 'Annulés',
                    'data' => $courses->pluck('cancelled')->toArray(),
                    'backgroundColor' => '#dc3545',
                ],
            ],
        ];
    }
    
    /**
     * Données graphique de présence
     * 
     * @param int $teacherId
     * @param Carbon $startDate
     * @return array
     */
    private function getAttendanceChartData(int $teacherId, Carbon $startDate): array
    {
        $attendance = DB::table('attendances')
            ->join('courses', 'attendances.course_id', '=', 'courses.id')
            ->where('courses.teacher_id', $teacherId)
            ->where('attendances.created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(attendances.created_at) as date'),
                DB::raw('SUM(CASE WHEN attendances.status = "present" THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as rate')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        return [
            'labels' => $attendance->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray(),
            'datasets' => [
                [
                    'label' => 'Taux de présence (%)',
                    'data' => $attendance->pluck('rate')->map(fn($r) => round($r, 1))->toArray(),
                    'borderColor' => '#800020',
                    'backgroundColor' => 'rgba(128, 0, 32, 0.1)',
                    'fill' => true,
                ],
            ],
        ];
    }
    
    /**
     * Données graphique des évaluations
     * 
     * @param int $teacherId
     * @param Carbon $startDate
     * @return array
     */
    private function getRatingsChartData(int $teacherId, Carbon $startDate): array
    {
        $ratings = DB::table('course_ratings')
            ->join('courses', 'course_ratings.course_id', '=', 'courses.id')
            ->where('courses.teacher_id', $teacherId)
            ->where('course_ratings.created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(course_ratings.created_at) as date'),
                DB::raw('AVG(course_ratings.rating) as avg_rating')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        return [
            'labels' => $ratings->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray(),
            'datasets' => [
                [
                    'label' => 'Note moyenne',
                    'data' => $ratings->pluck('avg_rating')->map(fn($r) => round($r, 1))->toArray(),
                    'borderColor' => '#ffc107',
                    'backgroundColor' => 'rgba(255, 193, 7, 0.1)',
                    'fill' => true,
                ],
            ],
        ];
    }
    
    /**
     * Rafraîchit les données du dashboard (AJAX)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $teacher = Auth::user();
        
        return response()->json([
            'kpis' => $this->getTeacherKPIs($teacher->id),
            'upcoming_courses' => $this->getUpcomingCourses($teacher->id),
            'live_courses' => $this->getLiveCourses($teacher->id),
            'notifications' => $this->getTeacherNotifications($teacher->id),
            'pending_tasks' => $this->getPendingTasks($teacher->id),
            'timestamp' => Carbon::now()->toIso8601String(),
        ]);
    }
}