<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Enrollment;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * AdminTeacherController
 * 
 * Gère la validation et le suivi des enseignants
 * Candidatures, performance, assignations, évaluations
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminTeacherController extends Controller
{
    /**
     * Affiche la liste des enseignants avec filtres
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = User::where('role', 'teacher');
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtre par spécialité
        if ($request->filled('specialty')) {
            $query->where('specialty', 'like', "%{$request->specialty}%");
        }
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Ajouter les compteurs
        $query->withCount(['courses', 'classes']);
        
        // Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Pagination
        $perPage = $request->input('per_page', 20);
        $teachers = $query->paginate($perPage)->withQueryString();
        
        // Ajouter les stats pour chaque enseignant
        foreach ($teachers as $teacher) {
            $teacher->stats = $this->getTeacherQuickStats($teacher);
        }
        
        // Statistiques globales
        $stats = [
            'total' => User::where('role', 'teacher')->count(),
            'active' => User::where('role', 'teacher')->where('status', 'active')->count(),
            'pending' => User::where('role', 'teacher')->where('status', 'pending')->count(),
            'suspended' => User::where('role', 'teacher')->where('status', 'suspended')->count(),
            'avg_rating' => $this->getAverageRating(),
            'total_courses' => Course::whereHas('teacher')->count(),
        ];
        
        return view('admin.teachers.index', compact('teachers', 'stats'));
    }
    
    /**
     * Affiche les candidatures en attente
     * 
     * @return View
     */
    public function pending(): View
    {
        $pendingTeachers = User::where('role', 'teacher')
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);
        
        return view('admin.teachers.pending', compact('pendingTeachers'));
    }
    
    /**
     * Affiche le détail d'une candidature
     * 
     * @param User $teacher
     * @return View
     */
    public function showApplication(User $teacher): View
    {
        // Vérifier que c'est bien un enseignant en attente
        if ($teacher->role !== 'teacher') {
            abort(404);
        }
        
        return view('admin.teachers.application', compact('teacher'));
    }
    
    /**
     * Approuve une candidature d'enseignant
     * 
     * @param User $teacher
     * @return RedirectResponse
     */
    public function approve(User $teacher): RedirectResponse
    {
        if ($teacher->role !== 'teacher') {
            return redirect()
                ->back()
                ->with('error', 'Cet utilisateur n\'est pas un enseignant.');
        }
        
        $teacher->update(['status' => 'active']);
        
        // TODO: Envoyer email de confirmation
        // Mail::to($teacher->email)->send(new TeacherApproved($teacher));
        
        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', "Candidature de {$teacher->name} approuvée avec succès !");
    }
    
    /**
     * Rejette une candidature d'enseignant
     * 
     * @param Request $request
     * @param User $teacher
     * @return RedirectResponse
     */
    public function reject(Request $request, User $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        
        if ($teacher->role !== 'teacher') {
            return redirect()
                ->back()
                ->with('error', 'Cet utilisateur n\'est pas un enseignant.');
        }
        
        $teacher->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
        ]);
        
        // TODO: Envoyer email de rejet avec raison
        // Mail::to($teacher->email)->send(new TeacherRejected($teacher, $validated['reason']));
        
        return redirect()
            ->route('admin.teachers.pending')
            ->with('success', 'Candidature rejetée.');
    }
    
    /**
     * Affiche les détails d'un enseignant
     * 
     * @param User $teacher
     * @return View
     */
    public function show(User $teacher): View
    {
        if ($teacher->role !== 'teacher') {
            abort(404);
        }
        
        // Charger les relations
        $teacher->load(['courses' => function ($query) {
            $query->latest()->limit(10);
        }]);
        
        // Statistiques détaillées
        $stats = $this->getTeacherDetailedStats($teacher);
        
        // Classes assignées
        $classes = ClassModel::whereHas('teachers', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->withCount('students')->get();
        
        // Matières enseignées
        $subjects = DB::table('class_teacher')
            ->join('subjects', 'class_teacher.subject_id', '=', 'subjects.id')
            ->where('class_teacher.teacher_id', $teacher->id)
            ->distinct('subjects.id')
            ->select('subjects.*')
            ->get();
        
        // Évaluations récentes
        $recentRatings = Grade::where('teacher_id', $teacher->id)
            ->with('student')
            ->latest()
            ->limit(10)
            ->get();
        
        // Disponibilités (si table exists)
        // $availabilities = Availability::where('teacher_id', $teacher->id)->get();
        
        return view('admin.teachers.show', compact(
            'teacher',
            'stats',
            'classes',
            'subjects',
            'recentRatings'
        ));
    }
    
    /**
     * Affiche le formulaire d'édition d'enseignant
     * 
     * @param User $teacher
     * @return View
     */
    public function edit(User $teacher): View
    {
        if ($teacher->role !== 'teacher') {
            abort(404);
        }
        
        // Toutes les matières disponibles
        $allSubjects = Subject::orderBy('name')->get();
        
        return view('admin.teachers.edit', compact('teacher', 'allSubjects'));
    }
    
    /**
     * Met à jour un enseignant
     * 
     * @param Request $request
     * @param User $teacher
     * @return RedirectResponse
     */
    public function update(Request $request, User $teacher): RedirectResponse
    {
        if ($teacher->role !== 'teacher') {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($teacher->id)],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:2000',
            'specialty' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0',
            'education' => 'nullable|string|max:500',
            'certifications' => 'nullable|string|max:1000',
            'hourly_rate' => 'nullable|numeric|min:0',
            'linkedin_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'status' => ['required', Rule::in(['active', 'pending', 'suspended', 'banned'])],
            'avatar' => 'nullable|image|max:2048',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);
        
        // Upload avatar
        if ($request->hasFile('avatar')) {
            if ($teacher->avatar) {
                Storage::disk('public')->delete($teacher->avatar);
            }
            $validated['avatar'] = $request->file('avatar')
                ->store('avatars', 'public');
        }
        
        // Upload CV
        if ($request->hasFile('cv_file')) {
            if ($teacher->cv_file) {
                Storage::disk('public')->delete($teacher->cv_file);
            }
            $validated['cv_file'] = $request->file('cv_file')
                ->store('cvs', 'public');
        }
        
        $teacher->update($validated);
        
        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', 'Enseignant mis à jour avec succès !');
    }
    
    /**
     * Suspend un enseignant
     * 
     * @param Request $request
     * @param User $teacher
     * @return RedirectResponse
     */
    public function suspend(Request $request, User $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        
        if ($teacher->role !== 'teacher') {
            abort(404);
        }
        
        // Vérifier les cours programmés
        $upcomingCourses = Course::where('teacher_id', $teacher->id)
            ->where('status', 'scheduled')
            ->where('start_time', '>', now())
            ->count();
        
        if ($upcomingCourses > 0) {
            return redirect()
                ->back()
                ->with('warning', "Attention : cet enseignant a {$upcomingCourses} cours programmés à venir.");
        }
        
        $teacher->update([
            'status' => 'suspended',
            'suspension_reason' => $validated['reason'],
            'suspended_at' => now(),
        ]);
        
        // TODO: Envoyer notification
        
        return redirect()
            ->back()
            ->with('success', 'Enseignant suspendu avec succès.');
    }
    
    /**
     * Réactive un enseignant
     * 
     * @param User $teacher
     * @return RedirectResponse
     */
    public function activate(User $teacher): RedirectResponse
    {
        if ($teacher->role !== 'teacher') {
            abort(404);
        }
        
        $teacher->update([
            'status' => 'active',
            'suspension_reason' => null,
            'suspended_at' => null,
        ]);
        
        // TODO: Envoyer notification
        
        return redirect()
            ->back()
            ->with('success', 'Enseignant réactivé avec succès !');
    }
    
    /**
     * Affiche les statistiques d'un enseignant
     * 
     * @param User $teacher
     * @return View
     */
    public function statistics(User $teacher): View
    {
        if ($teacher->role !== 'teacher') {
            abort(404);
        }
        
        // Stats sur les 12 derniers mois
        $monthlyStats = $this->getMonthlyStats($teacher, 12);
        
        // Stats par matière
        $statsBySubject = $this->getStatsBySubject($teacher);
        
        // Stats par classe
        $statsByClass = $this->getStatsByClass($teacher);
        
        // Évolution des évaluations
        $ratingsEvolution = $this->getRatingsEvolution($teacher);
        
        return view('admin.teachers.statistics', compact(
            'teacher',
            'monthlyStats',
            'statsBySubject',
            'statsByClass',
            'ratingsEvolution'
        ));
    }
    
    /**
     * Exporte les données d'un enseignant
     * 
     * @param User $teacher
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(User $teacher)
    {
        if ($teacher->role !== 'teacher') {
            abort(404);
        }
        
        $stats = $this->getTeacherDetailedStats($teacher);
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="teacher-' . $teacher->id . '-' . date('Y-m-d') . '.csv"',
        ];
        
        $callback = function() use ($teacher, $stats) {
            $file = fopen('php://output', 'w');
            
            // Informations générales
            fputcsv($file, ['Informations Enseignant']);
            fputcsv($file, ['Nom', $teacher->name]);
            fputcsv($file, ['Email', $teacher->email]);
            fputcsv($file, ['Spécialité', $teacher->specialty]);
            fputcsv($file, ['Statut', $teacher->status]);
            fputcsv($file, []);
            
            // Statistiques
            fputcsv($file, ['Statistiques']);
            fputcsv($file, ['Total Cours', $stats['courses']['total']]);
            fputcsv($file, ['Cours Complétés', $stats['courses']['completed']]);
            fputcsv($file, ['Total Étudiants', $stats['students']['total']]);
            fputcsv($file, ['Note Moyenne', $stats['rating']['average']]);
            fputcsv($file, ['Taux Assiduité', $stats['attendance_rate']]);
            fputcsv($file, []);
            
            // Cours récents
            fputcsv($file, ['Cours Récents']);
            fputcsv($file, ['Date', 'Matière', 'Classe', 'Statut', 'Présences']);
            
            $courses = Course::where('teacher_id', $teacher->id)
                ->with(['subject', 'class'])
                ->latest()
                ->limit(50)
                ->get();
            
            foreach ($courses as $course) {
                fputcsv($file, [
                    $course->start_time->format('Y-m-d H:i'),
                    $course->subject->name ?? 'N/A',
                    $course->class->name ?? 'N/A',
                    $course->status,
                    $course->attendances()->where('status', 'present')->count(),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Récupère les stats rapides d'un enseignant
     * 
     * @param User $teacher
     * @return array
     */
    private function getTeacherQuickStats(User $teacher): array
    {
        return [
            'courses_count' => Course::where('teacher_id', $teacher->id)->count(),
            'students_count' => DB::table('enrollments')
                ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                ->where('courses.teacher_id', $teacher->id)
                ->distinct('enrollments.user_id')
                ->count('enrollments.user_id'),
            'rating' => round(Grade::where('teacher_id', $teacher->id)->avg('rating') ?? 0, 1),
        ];
    }
    
    /**
     * Récupère les stats détaillées d'un enseignant
     * 
     * @param User $teacher
     * @return array
     */
    private function getTeacherDetailedStats(User $teacher): array
    {
        // Cours
        $totalCourses = Course::where('teacher_id', $teacher->id)->count();
        $completedCourses = Course::where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->count();
        $upcomingCourses = Course::where('teacher_id', $teacher->id)
            ->where('status', 'scheduled')
            ->where('start_time', '>', now())
            ->count();
        $totalHours = Course::where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, start_time, end_time)')) / 60;
        
        // Étudiants
        $totalStudents = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->where('courses.teacher_id', $teacher->id)
            ->distinct('enrollments.user_id')
            ->count('enrollments.user_id');
        
        $activeStudents = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->where('courses.teacher_id', $teacher->id)
            ->where('enrollments.status', 'active')
            ->distinct('enrollments.user_id')
            ->count('enrollments.user_id');
        
        // Évaluations
        $ratings = Grade::where('teacher_id', $teacher->id);
        $avgRating = round($ratings->avg('rating') ?? 0, 1);
        $totalRatings = $ratings->count();
        
        $ratingDistribution = [
            5 => Grade::where('teacher_id', $teacher->id)->where('rating', 5)->count(),
            4 => Grade::where('teacher_id', $teacher->id)->where('rating', 4)->count(),
            3 => Grade::where('teacher_id', $teacher->id)->where('rating', 3)->count(),
            2 => Grade::where('teacher_id', $teacher->id)->where('rating', 2)->count(),
            1 => Grade::where('teacher_id', $teacher->id)->where('rating', 1)->count(),
        ];
        
        // Assiduité
        $totalAttendances = Attendance::whereHas('course', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->count();
        
        $presentAttendances = Attendance::where('status', 'present')
            ->whereHas('course', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })->count();
        
        $attendanceRate = $totalAttendances > 0 
            ? ($presentAttendances / $totalAttendances) * 100 
            : 0;
        
        // Classes
        $classesCount = DB::table('class_teacher')
            ->where('teacher_id', $teacher->id)
            ->distinct('class_id')
            ->count();
        
        return [
            'courses' => [
                'total' => $totalCourses,
                'completed' => $completedCourses,
                'upcoming' => $upcomingCourses,
                'total_hours' => round($totalHours, 1),
            ],
            'students' => [
                'total' => $totalStudents,
                'active' => $activeStudents,
            ],
            'rating' => [
                'average' => $avgRating,
                'total' => $totalRatings,
                'distribution' => $ratingDistribution,
            ],
            'attendance_rate' => round($attendanceRate, 1),
            'classes_count' => $classesCount,
            'member_since' => $teacher->created_at->diffForHumans(),
        ];
    }
    
    /**
     * Récupère la note moyenne globale des enseignants
     * 
     * @return float
     */
    private function getAverageRating(): float
    {
        return round(
            Grade::whereHas('teacher', function ($query) {
                $query->where('role', 'teacher');
            })->avg('rating') ?? 0,
            1
        );
    }
    
    /**
     * Récupère les stats mensuelles
     * 
     * @param User $teacher
     * @param int $months
     * @return array
     */
    private function getMonthlyStats(User $teacher, int $months = 12): array
    {
        $startDate = Carbon::now()->subMonths($months);
        
        $monthlyData = Course::where('teacher_id', $teacher->id)
            ->where('start_time', '>=', $startDate)
            ->selectRaw('
                YEAR(start_time) as year,
                MONTH(start_time) as month,
                COUNT(*) as courses_count,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_count
            ')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        return $monthlyData->toArray();
    }
    
    /**
     * Récupère les stats par matière
     * 
     * @param User $teacher
     * @return array
     */
    private function getStatsBySubject(User $teacher): array
    {
        return DB::table('courses')
            ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
            ->where('courses.teacher_id', $teacher->id)
            ->selectRaw('
                subjects.name,
                COUNT(*) as courses_count,
                SUM(CASE WHEN courses.status = "completed" THEN 1 ELSE 0 END) as completed_count
            ')
            ->groupBy('subjects.id', 'subjects.name')
            ->get()
            ->toArray();
    }
    
    /**
     * Récupère les stats par classe
     * 
     * @param User $teacher
     * @return array
     */
    private function getStatsByClass(User $teacher): array
    {
        return DB::table('courses')
            ->join('classes', 'courses.class_id', '=', 'classes.id')
            ->where('courses.teacher_id', $teacher->id)
            ->selectRaw('
                classes.name,
                COUNT(*) as courses_count,
                SUM(CASE WHEN courses.status = "completed" THEN 1 ELSE 0 END) as completed_count
            ')
            ->groupBy('classes.id', 'classes.name')
            ->get()
            ->toArray();
    }
    
    /**
     * Récupère l'évolution des évaluations
     * 
     * @param User $teacher
     * @return array
     */
    private function getRatingsEvolution(User $teacher): array
    {
        return Grade::where('teacher_id', $teacher->id)
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                AVG(rating) as avg_rating,
                COUNT(*) as ratings_count
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }
}