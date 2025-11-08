<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Formation;
use App\Models\ClassModel;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Attendance;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * AdminDashboardController
 * 
 * Gère le tableau de bord principal de l'administrateur
 * Affiche les KPIs globaux, statistiques temps réel et monitoring système
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminDashboardController extends Controller
{
    /**
     * Affiche le tableau de bord administrateur
     * 
     * @return View
     */
    public function index(): View
    {
        // KPIs principaux (avec cache 5 minutes)
        $kpis = Cache::remember('admin_kpis', 300, function () {
            return $this->getGlobalKPIs();
        });
        
        // Statistiques en temps réel (pas de cache)
        $realtimeStats = $this->getRealtimeStats();
        
        // Alertes importantes
        $alerts = $this->getSystemAlerts();
        
        // Dernières activités (10 dernières)
        $recentActivities = $this->getRecentActivities(10);
        
        // Données pour graphiques (30 derniers jours)
        $chartData = $this->getChartData(30);
        
        // Top formations
        $topFormations = $this->getTopFormations(5);
        
        // Top enseignants
        $topTeachers = $this->getTopTeachers(5);
        
        // Statistiques financières
        $financialStats = $this->getFinancialStats();
        
        return view('admin.dashboard.index', compact(
            'kpis',
            'realtimeStats',
            'alerts',
            'recentActivities',
            'chartData',
            'topFormations',
            'topTeachers',
            'financialStats'
        ));
    }
    
    /**
     * Récupère les KPIs globaux de la plateforme
     * 
     * @return array
     */
    private function getGlobalKPIs(): array
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();
        
        // Total utilisateurs par rôle
        $totalUsers = User::count();
        $totalStudents = User::where('role', 'student')->count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        
        // Croissance utilisateurs (vs mois dernier)
        $newUsersThisMonth = User::where('created_at', '>=', $lastMonth)->count();
        $newUsersLastMonth = User::whereBetween('created_at', [
            $lastMonth->copy()->subMonth(),
            $lastMonth
        ])->count();
        $userGrowth = $newUsersLastMonth > 0 
            ? (($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100 
            : 0;
        
        // Formations et classes
        $totalFormations = Formation::where('status', 'active')->count();
        $totalClasses = ClassModel::count();
        
        // Cours
        $totalCourses = Course::count();
        $liveCoursesCount = Course::where('status', 'live')->count();
        $scheduledCoursesCount = Course::where('status', 'scheduled')
            ->where('start_time', '>', $now)
            ->count();
        
        // Inscriptions
        $totalEnrollments = Enrollment::count();
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        $completedEnrollments = Enrollment::where('status', 'completed')->count();
        
        // Taux de complétion
        $completionRate = $totalEnrollments > 0 
            ? ($completedEnrollments / $totalEnrollments) * 100 
            : 0;
        
        // Revenus
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $revenueThisMonth = Payment::where('status', 'completed')
            ->where('paid_at', '>=', $lastMonth)
            ->sum('amount');
        $revenueLastMonth = Payment::where('status', 'completed')
            ->whereBetween('paid_at', [
                $lastMonth->copy()->subMonth(),
                $lastMonth
            ])
            ->sum('amount');
        $revenueGrowth = $revenueLastMonth > 0 
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100 
            : 0;
        
        // Taux d'assiduité global
        $totalAttendances = Attendance::count();
        $presentAttendances = Attendance::where('status', 'present')->count();
        $attendanceRate = $totalAttendances > 0 
            ? ($presentAttendances / $totalAttendances) * 100 
            : 0;
        
        // Note moyenne globale
        $averageGrade = Grade::avg('grade') ?? 0;
        
        return [
            'users' => [
                'total' => $totalUsers,
                'students' => $totalStudents,
                'teachers' => $totalTeachers,
                'admins' => $totalAdmins,
                'growth' => round($userGrowth, 1),
                'new_this_month' => $newUsersThisMonth,
            ],
            'formations' => [
                'total' => $totalFormations,
                'classes' => $totalClasses,
            ],
            'courses' => [
                'total' => $totalCourses,
                'live' => $liveCoursesCount,
                'scheduled' => $scheduledCoursesCount,
            ],
            'enrollments' => [
                'total' => $totalEnrollments,
                'active' => $activeEnrollments,
                'completed' => $completedEnrollments,
                'completion_rate' => round($completionRate, 1),
            ],
            'revenue' => [
                'total' => round($totalRevenue, 2),
                'this_month' => round($revenueThisMonth, 2),
                'growth' => round($revenueGrowth, 1),
                'currency' => 'EUR',
            ],
            'performance' => [
                'attendance_rate' => round($attendanceRate, 1),
                'average_grade' => round($averageGrade, 1),
            ],
        ];
    }
    
    /**
     * Récupère les statistiques temps réel
     * 
     * @return array
     */
    private function getRealtimeStats(): array
    {
        $now = Carbon::now();
        
        // Utilisateurs connectés (actifs dans les 15 dernières minutes)
        $onlineUsers = User::where('last_activity_at', '>=', $now->copy()->subMinutes(15))
            ->count();
        
        // Cours en direct actuellement
        $liveNow = Course::where('status', 'live')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->count();
        
        // Prochains cours (dans l'heure)
        $upcomingSoon = Course::where('status', 'scheduled')
            ->whereBetween('start_time', [$now, $now->copy()->addHour()])
            ->count();
        
        // Inscriptions aujourd'hui
        $enrollmentsToday = Enrollment::whereDate('created_at', $now->toDateString())
            ->count();
        
        // Paiements aujourd'hui
        $paymentsToday = Payment::where('status', 'completed')
            ->whereDate('paid_at', $now->toDateString())
            ->count();
        
        $revenueToday = Payment::where('status', 'completed')
            ->whereDate('paid_at', $now->toDateString())
            ->sum('amount');
        
        return [
            'online_users' => $onlineUsers,
            'live_courses' => $liveNow,
            'upcoming_soon' => $upcomingSoon,
            'enrollments_today' => $enrollmentsToday,
            'payments_today' => $paymentsToday,
            'revenue_today' => round($revenueToday, 2),
        ];
    }
    
    /**
     * Récupère les alertes système importantes
     * 
     * @return array
     */
    private function getSystemAlerts(): array
    {
        $alerts = [];
        
        // Cours sans enseignant assigné
        $coursesWithoutTeacher = Course::whereNull('teacher_id')
            ->where('status', 'scheduled')
            ->count();
        if ($coursesWithoutTeacher > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'exclamation-triangle',
                'message' => "{$coursesWithoutTeacher} cours programmés sans enseignant assigné",
                'link' => route('admin.courses.index', ['filter' => 'no_teacher']),
            ];
        }
        
        // Inscriptions en attente de validation
        $pendingTeachers = User::where('role', 'teacher')
            ->where('status', 'pending')
            ->count();
        if ($pendingTeachers > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'user-clock',
                'message' => "{$pendingTeachers} candidatures d'enseignants en attente",
                'link' => route('admin.teachers.pending'),
            ];
        }
        
        // Paiements échoués aujourd'hui
        $failedPayments = Payment::where('status', 'failed')
            ->whereDate('created_at', Carbon::today())
            ->count();
        if ($failedPayments > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'exclamation-circle',
                'message' => "{$failedPayments} paiements échoués aujourd'hui",
                'link' => route('admin.finance.failed-payments'),
            ];
        }
        
        // Classes surchargées (plus de 30 étudiants)
        $overloadedClasses = ClassModel::withCount('students')
            ->having('students_count', '>', 30)
            ->count();
        if ($overloadedClasses > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'users',
                'message' => "{$overloadedClasses} classes avec plus de 30 étudiants",
                'link' => route('admin.classes.index', ['filter' => 'overloaded']),
            ];
        }
        
        // Stockage serveur (si > 80%)
        $diskUsage = $this->getDiskUsage();
        if ($diskUsage > 80) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'hdd',
                'message' => "Stockage serveur à {$diskUsage}% de capacité",
                'link' => route('admin.system.storage'),
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Récupère les dernières activités
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecentActivities(int $limit = 10)
    {
        // TODO: Implémenter avec une table activity_logs
        // Pour l'instant, on retourne les dernières inscriptions
        return Enrollment::with(['student', 'formation'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($enrollment) {
                return [
                    'type' => 'enrollment',
                    'icon' => 'user-plus',
                    'message' => "{$enrollment->student->name} s'est inscrit à {$enrollment->formation->name}",
                    'time' => $enrollment->created_at->diffForHumans(),
                    'timestamp' => $enrollment->created_at,
                ];
            });
    }
    
    /**
     * Récupère les données pour les graphiques
     * 
     * @param int $days
     * @return array
     */
    private function getChartData(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();
        
        // Inscriptions par jour
        $enrollmentsByDay = Enrollment::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
        
        // Revenus par jour
        $revenueByDay = Payment::selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->where('status', 'completed')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();
        
        // Présences par jour
        $attendanceByDay = Attendance::selectRaw('DATE(created_at) as date, 
            SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
            COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => [
                    'rate' => $item->total > 0 ? ($item->present / $item->total) * 100 : 0
                ]];
            })
            ->toArray();
        
        // Créer un tableau complet avec toutes les dates
        $labels = [];
        $enrollmentsData = [];
        $revenueData = [];
        $attendanceData = [];
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->toDateString();
            $labels[] = $date->format('d/m');
            $enrollmentsData[] = $enrollmentsByDay[$dateStr] ?? 0;
            $revenueData[] = round($revenueByDay[$dateStr] ?? 0, 2);
            $attendanceData[] = round($attendanceByDay[$dateStr]['rate'] ?? 0, 1);
        }
        
        return [
            'labels' => $labels,
            'enrollments' => $enrollmentsData,
            'revenue' => $revenueData,
            'attendance' => $attendanceData,
        ];
    }
    
    /**
     * Récupère les top formations
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTopFormations(int $limit = 5)
    {
        return Formation::withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->limit($limit)
            ->get()
            ->map(function ($formation) {
                $revenue = Payment::where('formation_id', $formation->id)
                    ->where('status', 'completed')
                    ->sum('amount');
                
                return [
                    'id' => $formation->id,
                    'name' => $formation->name,
                    'enrollments' => $formation->enrollments_count,
                    'revenue' => round($revenue, 2),
                    'completion_rate' => $formation->completion_rate ?? 0,
                ];
            });
    }
    
    /**
     * Récupère les top enseignants
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTopTeachers(int $limit = 5)
    {
        return User::where('role', 'teacher')
            ->where('status', 'active')
            ->withCount([
                'courses' => function ($query) {
                    $query->where('status', 'completed');
                }
            ])
            ->with(['grades' => function ($query) {
                $query->selectRaw('teacher_id, AVG(rating) as avg_rating')
                    ->groupBy('teacher_id');
            }])
            ->orderByDesc('courses_count')
            ->limit($limit)
            ->get()
            ->map(function ($teacher) {
                $totalStudents = DB::table('enrollments')
                    ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                    ->where('courses.teacher_id', $teacher->id)
                    ->distinct('enrollments.user_id')
                    ->count('enrollments.user_id');
                
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'avatar' => $teacher->avatar ?? '/images/default-avatar.png',
                    'courses_count' => $teacher->courses_count,
                    'students_count' => $totalStudents,
                    'rating' => round($teacher->grades->first()->avg_rating ?? 0, 1),
                ];
            });
    }
    
    /**
     * Récupère les statistiques financières
     * 
     * @return array
     */
    private function getFinancialStats(): array
    {
        $now = Carbon::now();
        
        // Revenus mensuels (12 derniers mois)
        $monthlyRevenue = Payment::selectRaw('
                YEAR(paid_at) as year,
                MONTH(paid_at) as month,
                SUM(amount) as total
            ')
            ->where('status', 'completed')
            ->where('paid_at', '>=', $now->copy()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        // Chiffre d'affaires annuel
        $annualRevenue = Payment::where('status', 'completed')
            ->whereYear('paid_at', $now->year)
            ->sum('amount');
        
        // Paiements en attente
        $pendingPayments = Payment::where('status', 'pending')->sum('amount');
        
        // Taux de conversion
        $totalVisitors = 10000; // TODO: Intégrer avec Google Analytics
        $totalEnrollments = Enrollment::whereYear('created_at', $now->year)->count();
        $conversionRate = $totalVisitors > 0 ? ($totalEnrollments / $totalVisitors) * 100 : 0;
        
        return [
            'annual_revenue' => round($annualRevenue, 2),
            'pending_amount' => round($pendingPayments, 2),
            'conversion_rate' => round($conversionRate, 2),
            'monthly_data' => $monthlyRevenue,
        ];
    }
    
    /**
     * Récupère l'utilisation du disque
     * 
     * @return float Pourcentage d'utilisation
     */
    private function getDiskUsage(): float
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;
        
        return ($used / $total) * 100;
    }
    
    /**
     * Export des statistiques en PDF
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportStats(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonth());
        $endDate = $request->input('end_date', Carbon::now());
        
        // TODO: Implémenter avec DomPDF ou similar
        // $pdf = PDF::loadView('admin.reports.stats', compact('startDate', 'endDate'));
        // return $pdf->download('statistiques-infinischool.pdf');
        
        return response()->json([
            'message' => 'Export PDF en développement',
        ]);
    }
    
    /**
     * Rafraîchit les statistiques (vide le cache)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshStats()
    {
        Cache::forget('admin_kpis');
        
        return response()->json([
            'success' => true,
            'message' => 'Statistiques rafraîchies avec succès',
        ]);
    }
}