<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * TeacherAnalyticsController
 * 
 * Gère les statistiques et analytics avancées pour l'enseignant
 * Fournit des insights sur les performances, engagement et progression
 * 
 * @package App\Http\Controllers\Teacher
 */
class TeacherAnalyticsController extends Controller
{
    /**
     * Vue d'ensemble des analytics
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $teacher = Auth::user();
        $period = $request->input('period', '30days');
        
        $startDate = $this->getPeriodStartDate($period);
        
        // KPIs généraux
        $kpis = $this->getAnalyticsKPIs($teacher->id, $startDate);
        
        // Graphiques principaux
        $coursesChart = $this->getCoursesChartData($teacher->id, $startDate);
        $attendanceChart = $this->getAttendanceChartData($teacher->id, $startDate);
        $gradesChart = $this->getGradesChartData($teacher->id, $startDate);
        $engagementChart = $this->getEngagementChartData($teacher->id, $startDate);
        
        // Top étudiants et classes
        $topStudents = $this->getTopStudents($teacher->id, $startDate);
        $classPerformance = $this->getClassPerformance($teacher->id, $startDate);
        
        return view('teacher.analytics.index', compact(
            'period',
            'kpis',
            'coursesChart',
            'attendanceChart',
            'gradesChart',
            'engagementChart',
            'topStudents',
            'classPerformance'
        ));
    }
    
    /**
     * Analytics détaillées par classe
     * 
     * @param ClassModel $class
     * @param Request $request
     * @return View
     */
    public function classAnalytics(ClassModel $class, Request $request): View
    {
        $this->authorize('view', $class);
        
        $teacher = Auth::user();
        $period = $request->input('period', '30days');
        $startDate = $this->getPeriodStartDate($period);
        
        // Vue d'ensemble de la classe
        $classOverview = [
            'total_students' => $class->students->count(),
            'active_students' => $class->students->where('status', 'active')->count(),
            'avg_attendance' => $this->getClassAttendanceRate($class->id, $teacher->id, $startDate),
            'avg_grade' => $this->getClassAverageGrade($class->id, $teacher->id, $startDate),
            'engagement_rate' => $this->getClassEngagementRate($class->id, $teacher->id, $startDate),
        ];
        
        // Progression de la classe dans le temps
        $progressionData = $this->getClassProgressionData($class->id, $teacher->id, $startDate);
        
        // Distribution des performances
        $performanceDistribution = $this->getPerformanceDistribution($class->id, $teacher->id);
        
        // Étudiants à risque
        $atRiskStudents = $this->getAtRiskStudents($class->id, $teacher->id);
        
        // Étudiants excellents
        $topPerformers = $this->getTopPerformers($class->id, $teacher->id);
        
        // Analytics par matière
        $subjectAnalytics = $this->getSubjectAnalytics($class->id, $teacher->id, $startDate);
        
        return view('teacher.analytics.class', compact(
            'class',
            'period',
            'classOverview',
            'progressionData',
            'performanceDistribution',
            'atRiskStudents',
            'topPerformers',
            'subjectAnalytics'
        ));
    }
    
    /**
     * Analytics détaillées par étudiant
     * 
     * @param ClassModel $class
     * @param User $student
     * @param Request $request
     * @return View
     */
    public function studentAnalytics(ClassModel $class, User $student, Request $request): View
    {
        $this->authorize('view', $class);
        
        if (!$class->students->contains($student)) {
            abort(404, 'Étudiant non trouvé dans cette classe.');
        }
        
        $teacher = Auth::user();
        $period = $request->input('period', '30days');
        $startDate = $this->getPeriodStartDate($period);
        
        // Profil de performance de l'étudiant
        $performanceProfile = [
            'avg_grade' => $this->getStudentAverageGrade($student->id, $class->id, $teacher->id),
            'attendance_rate' => $this->getStudentAttendanceRate($student->id, $class->id, $teacher->id),
            'assignment_completion' => $this->getStudentAssignmentCompletion($student->id, $class->id, $teacher->id),
            'participation_score' => $this->getStudentParticipationScore($student->id, $class->id, $teacher->id),
            'engagement_level' => $this->getStudentEngagementLevel($student->id, $class->id, $teacher->id),
        ];
        
        // Évolution des notes
        $gradesEvolution = $this->getStudentGradesEvolution($student->id, $class->id, $teacher->id, $startDate);
        
        // Présence dans le temps
        $attendanceTrend = $this->getStudentAttendanceTrend($student->id, $class->id, $teacher->id, $startDate);
        
        // Comparaison avec la moyenne de la classe
        $classComparison = $this->getStudentClassComparison($student->id, $class->id, $teacher->id);
        
        // Points forts et faibles
        $strengths = $this->getStudentStrengths($student->id, $class->id, $teacher->id);
        $weaknesses = $this->getStudentWeaknesses($student->id, $class->id, $teacher->id);
        
        // Recommandations
        $recommendations = $this->getStudentRecommendations($performanceProfile, $strengths, $weaknesses);
        
        return view('teacher.analytics.student', compact(
            'class',
            'student',
            'period',
            'performanceProfile',
            'gradesEvolution',
            'attendanceTrend',
            'classComparison',
            'strengths',
            'weaknesses',
            'recommendations'
        ));
    }
    
    /**
     * Export des analytics (PDF/Excel)
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $teacher = Auth::user();
        $format = $request->input('format', 'pdf'); // pdf, excel
        $type = $request->input('type', 'overview'); // overview, class, student
        $period = $request->input('period', '30days');
        
        $startDate = $this->getPeriodStartDate($period);
        
        // Générer les données selon le type
        $data = match($type) {
            'class' => $this->generateClassReport($request->input('class_id'), $teacher->id, $startDate),
            'student' => $this->generateStudentReport(
                $request->input('class_id'),
                $request->input('student_id'),
                $teacher->id,
                $startDate
            ),
            default => $this->generateOverviewReport($teacher->id, $startDate),
        };
        
        // Générer le fichier selon le format
        return match($format) {
            'excel' => $this->exportToExcel($data, $type),
            default => $this->exportToPDF($data, $type),
        };
    }
    
    /**
     * API: Récupère les données pour les graphiques (AJAX)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getChartData(Request $request): JsonResponse
    {
        $teacher = Auth::user();
        $chartType = $request->input('type'); // courses, attendance, grades, engagement
        $period = $request->input('period', '30days');
        $classId = $request->input('class_id');
        
        $startDate = $this->getPeriodStartDate($period);
        
        $data = match($chartType) {
            'courses' => $this->getCoursesChartData($teacher->id, $startDate, $classId),
            'attendance' => $this->getAttendanceChartData($teacher->id, $startDate, $classId),
            'grades' => $this->getGradesChartData($teacher->id, $startDate, $classId),
            'engagement' => $this->getEngagementChartData($teacher->id, $startDate, $classId),
            default => null,
        };
        
        if (!$data) {
            return response()->json(['error' => 'Type de graphique invalide'], 400);
        }
        
        return response()->json($data);
    }
    
    /**
     * Récupère les KPIs analytics
     */
    private function getAnalyticsKPIs(int $teacherId, Carbon $startDate): array
    {
        return [
            'total_courses' => Course::where('teacher_id', $teacherId)
                ->where('scheduled_at', '>=', $startDate)
                ->count(),
            'avg_attendance' => $this->getOverallAttendanceRate($teacherId, $startDate),
            'avg_grade' => $this->getOverallAverageGrade($teacherId, $startDate),
            'student_engagement' => $this->getOverallEngagementRate($teacherId, $startDate),
            'total_hours' => Course::where('teacher_id', $teacherId)
                ->where('scheduled_at', '>=', $startDate)
                ->where('status', 'completed')
                ->sum('duration') / 60,
            'content_views' => $this->getTotalContentViews($teacherId, $startDate),
        ];
    }
    
    /**
     * Données graphique des cours
     */
    private function getCoursesChartData(int $teacherId, Carbon $startDate, ?int $classId = null): array
    {
        $courses = Course::where('teacher_id', $teacherId)
            ->where('scheduled_at', '>=', $startDate)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->select(
                DB::raw('DATE(scheduled_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled'),
                DB::raw('SUM(duration) as total_minutes')
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
                    'borderColor' => '#800020',
                    'backgroundColor' => 'rgba(128, 0, 32, 0.1)',
                ],
                [
                    'label' => 'Complétés',
                    'data' => $courses->pluck('completed')->toArray(),
                    'borderColor' => '#28a745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                ],
            ],
        ];
    }
    
    /**
     * Données graphique de présence
     */
    private function getAttendanceChartData(int $teacherId, Carbon $startDate, ?int $classId = null): array
    {
        $attendance = DB::table('attendances')
            ->join('courses', 'attendances.course_id', '=', 'courses.id')
            ->where('courses.teacher_id', $teacherId)
            ->when($classId, fn($q) => $q->where('courses.class_id', $classId))
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
     * Données graphique des notes
     */
    private function getGradesChartData(int $teacherId, Carbon $startDate, ?int $classId = null): array
    {
        $grades = DB::table('grades')
            ->join('assignments', 'grades.assignment_id', '=', 'assignments.id')
            ->join('courses', 'assignments.course_id', '=', 'courses.id')
            ->where('courses.teacher_id', $teacherId)
            ->when($classId, fn($q) => $q->where('courses.class_id', $classId))
            ->where('grades.created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(grades.created_at) as date'),
                DB::raw('AVG(grades.grade) as avg_grade'),
                DB::raw('MIN(grades.grade) as min_grade'),
                DB::raw('MAX(grades.grade) as max_grade')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        return [
            'labels' => $grades->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray(),
            'datasets' => [
                [
                    'label' => 'Moyenne',
                    'data' => $grades->pluck('avg_grade')->map(fn($g) => round($g, 1))->toArray(),
                    'borderColor' => '#800020',
                    'backgroundColor' => 'rgba(128, 0, 32, 0.1)',
                    'fill' => true,
                ],
            ],
        ];
    }
    
    /**
     * Données graphique d'engagement
     */
    private function getEngagementChartData(int $teacherId, Carbon $startDate, ?int $classId = null): array
    {
        // Simulé - À implémenter selon les métriques d'engagement réelles
        $days = Carbon::parse($startDate)->diffInDays(Carbon::now());
        $data = [];
        
        for ($i = 0; $i <= $days; $i++) {
            $date = Carbon::parse($startDate)->addDays($i);
            $engagement = $this->calculateDailyEngagement($teacherId, $date, $classId);
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'engagement' => $engagement,
            ];
        }
        
        return [
            'labels' => array_column($data, 'date'),
            'datasets' => [
                [
                    'label' => 'Taux d\'engagement (%)',
                    'data' => array_column($data, 'engagement'),
                    'borderColor' => '#ffc107',
                    'backgroundColor' => 'rgba(255, 193, 7, 0.1)',
                    'fill' => true,
                ],
            ],
        ];
    }
    
    /**
     * Top étudiants par performance
     */
    private function getTopStudents(int $teacherId, Carbon $startDate, int $limit = 10): array
    {
        return DB::table('users')
            ->join('enrollments', 'users.id', '=', 'enrollments.user_id')
            ->join('classes', 'enrollments.class_id', '=', 'classes.id')
            ->join('class_teacher', function($join) use ($teacherId) {
                $join->on('classes.id', '=', 'class_teacher.class_id')
                     ->where('class_teacher.teacher_id', $teacherId);
            })
            ->leftJoin('grades', function($join) use ($teacherId) {
                $join->on('users.id', '=', 'grades.user_id')
                     ->join('assignments', 'grades.assignment_id', '=', 'assignments.id')
                     ->join('courses', 'assignments.course_id', '=', 'courses.id')
                     ->where('courses.teacher_id', $teacherId);
            })
            ->where('users.role', 'student')
            ->where('grades.created_at', '>=', $startDate)
            ->select([
                'users.id',
                'users.first_name',
                'users.last_name',
                DB::raw('AVG(grades.grade) as avg_grade'),
                DB::raw('COUNT(grades.id) as total_grades'),
            ])
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->orderBy('avg_grade', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
    
    /**
     * Performance par classe
     */
    private function getClassPerformance(int $teacherId, Carbon $startDate): array
    {
        return DB::table('classes')
            ->join('class_teacher', 'classes.id', '=', 'class_teacher.class_id')
            ->leftJoin('courses', function($join) use ($teacherId) {
                $join->on('classes.id', '=', 'courses.class_id')
                     ->where('courses.teacher_id', $teacherId);
            })
            ->leftJoin('grades', function($join) use ($startDate) {
                $join->on('courses.id', '=', 'grades.assignment_id')
                     ->where('grades.created_at', '>=', $startDate);
            })
            ->where('class_teacher.teacher_id', $teacherId)
            ->select([
                'classes.id',
                'classes.name',
                DB::raw('AVG(grades.grade) as avg_grade'),
                DB::raw('COUNT(DISTINCT courses.id) as total_courses'),
            ])
            ->groupBy('classes.id', 'classes.name')
            ->orderBy('avg_grade', 'desc')
            ->get()
            ->toArray();
    }
    
    /**
     * Calcule le taux d'engagement quotidien
     */
    private function calculateDailyEngagement(int $teacherId, Carbon $date, ?int $classId): float
    {
        // Logique de calcul d'engagement basée sur:
        // - Participation aux cours
        // - Soumission de devoirs
        // - Accès aux ressources
        // - Messages/interactions
        
        // Simulé pour l'exemple
        return rand(70, 95);
    }
    
    /**
     * Helpers pour les différentes métriques
     */
    
    private function getPeriodStartDate(string $period): Carbon
    {
        return match($period) {
            '7days' => Carbon::now()->subDays(7),
            '30days' => Carbon::now()->subDays(30),
            '3months' => Carbon::now()->subMonths(3),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(30),
        };
    }
    
    private function getOverallAttendanceRate(int $teacherId, Carbon $startDate): float
    {
        // Implémentation similaire aux méthodes précédentes
        return 85.5; // Simulé
    }
    
    private function getOverallAverageGrade(int $teacherId, Carbon $startDate): float
    {
        return 78.2; // Simulé
    }
    
    private function getOverallEngagementRate(int $teacherId, Carbon $startDate): float
    {
        return 82.7; // Simulé
    }
    
    private function getTotalContentViews(int $teacherId, Carbon $startDate): int
    {
        return 8437; // Simulé
    }
    
    // Autres méthodes helpers à implémenter selon les besoins...
    
    private function getClassAttendanceRate(int $classId, int $teacherId, Carbon $startDate): float
    {
        return 0.0;
    }
    
    private function getClassAverageGrade(int $classId, int $teacherId, Carbon $startDate): float
    {
        return 0.0;
    }
    
    private function getClassEngagementRate(int $classId, int $teacherId, Carbon $startDate): float
    {
        return 0.0;
    }
    
    private function getClassProgressionData(int $classId, int $teacherId, Carbon $startDate): array
    {
        return [];
    }
    
    private function getPerformanceDistribution(int $classId, int $teacherId): array
    {
        return [];
    }
    
    private function getAtRiskStudents(int $classId, int $teacherId): array
    {
        return [];
    }
    
    private function getTopPerformers(int $classId, int $teacherId): array
    {
        return [];
    }
    
    private function getSubjectAnalytics(int $classId, int $teacherId, Carbon $startDate): array
    {
        return [];
    }
    
    private function getStudentAverageGrade(int $studentId, int $classId, int $teacherId): float
    {
        return 0.0;
    }
    
    private function getStudentAttendanceRate(int $studentId, int $classId, int $teacherId): float
    {
        return 0.0;
    }
    
    private function getStudentAssignmentCompletion(int $studentId, int $classId, int $teacherId): float
    {
        return 0.0;
    }
    
    private function getStudentParticipationScore(int $studentId, int $classId, int $teacherId): float
    {
        return 0.0;
    }
    
    private function getStudentEngagementLevel(int $studentId, int $classId, int $teacherId): string
    {
        return 'high';
    }
    
    private function getStudentGradesEvolution(int $studentId, int $classId, int $teacherId, Carbon $startDate): array
    {
        return [];
    }
    
    private function getStudentAttendanceTrend(int $studentId, int $classId, int $teacherId, Carbon $startDate): array
    {
        return [];
    }
    
    private function getStudentClassComparison(int $studentId, int $classId, int $teacherId): array
    {
        return [];
    }
    
    private function getStudentStrengths(int $studentId, int $classId, int $teacherId): array
    {
        return [];
    }
    
    private function getStudentWeaknesses(int $studentId, int $classId, int $teacherId): array
    {
        return [];
    }
    
    private function getStudentRecommendations(array $profile, array $strengths, array $weaknesses): array
    {
        return [];
    }
    
    private function generateOverviewReport(int $teacherId, Carbon $startDate): array
    {
        return [];
    }
    
    private function generateClassReport(int $classId, int $teacherId, Carbon $startDate): array
    {
        return [];
    }
    
    private function generateStudentReport(int $classId, int $studentId, int $teacherId, Carbon $startDate): array
    {
        return [];
    }
    
    private function exportToExcel(array $data, string $type)
    {
        // À implémenter
    }
    
    private function exportToPDF(array $data, string $type)
    {
        // À implémenter
    }
}