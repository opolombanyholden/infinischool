<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Certificate;
use App\Models\Course;
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
 * AdminStudentController
 * 
 * Gère la gestion complète des étudiants
 * Profils, inscriptions, progression, notes, paiements
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminStudentController extends Controller
{
    /**
     * Affiche la liste des étudiants avec filtres
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = User::where('role', 'student');
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtre par formation
        if ($request->filled('formation_id')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('formation_id', $request->formation_id);
            });
        }
        
        // Filtre par classe
        if ($request->filled('class_id')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }
        
        // Filtre par date d'inscription
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
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
        $query->withCount(['enrollments', 'grades', 'attendances']);
        
        // Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Pagination
        $perPage = $request->input('per_page', 25);
        $students = $query->paginate($perPage)->withQueryString();
        
        // Ajouter les stats rapides pour chaque étudiant
        foreach ($students as $student) {
            $student->quick_stats = $this->getStudentQuickStats($student);
        }
        
        // Statistiques globales
        $stats = [
            'total' => User::where('role', 'student')->count(),
            'active' => User::where('role', 'student')->where('status', 'active')->count(),
            'pending' => User::where('role', 'student')->where('status', 'pending')->count(),
            'suspended' => User::where('role', 'student')->where('status', 'suspended')->count(),
            'total_enrollments' => Enrollment::count(),
            'active_enrollments' => Enrollment::where('status', 'active')->count(),
        ];
        
        // Formations et classes pour les filtres
        $formations = Formation::where('status', 'active')->orderBy('name')->get();
        $classes = ClassModel::where('status', 'active')->orderBy('name')->get();
        
        return view('admin.students.index', compact('students', 'stats', 'formations', 'classes'));
    }
    
    /**
     * Affiche le profil détaillé d'un étudiant
     * 
     * @param User $student
     * @return View
     */
    public function show(User $student): View
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        // Charger les relations
        $student->load([
            'enrollments.formation',
            'enrollments.class',
            'grades',
            'certificates',
        ]);
        
        // Statistiques détaillées
        $stats = $this->getStudentDetailedStats($student);
        
        // Inscriptions actives
        $activeEnrollments = Enrollment::where('user_id', $student->id)
            ->where('status', 'active')
            ->with(['formation', 'class'])
            ->get();
        
        // Historique des cours suivis
        $coursesHistory = Attendance::where('user_id', $student->id)
            ->with(['course.subject', 'course.teacher'])
            ->latest()
            ->limit(10)
            ->get();
        
        // Paiements
        $payments = Payment::where('student_id', $student->id)
            ->with('formation')
            ->latest()
            ->limit(10)
            ->get();
        
        // Notes récentes
        $recentGrades = Grade::where('user_id', $student->id)
            ->with(['subject', 'teacher'])
            ->latest()
            ->limit(10)
            ->get();
        
        // Certificats
        $certificates = Certificate::where('user_id', $student->id)
            ->with('formation')
            ->latest()
            ->get();
        
        return view('admin.students.show', compact(
            'student',
            'stats',
            'activeEnrollments',
            'coursesHistory',
            'payments',
            'recentGrades',
            'certificates'
        ));
    }
    
    /**
     * Affiche le formulaire d'édition d'un étudiant
     * 
     * @param User $student
     * @return View
     */
    public function edit(User $student): View
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        return view('admin.students.edit', compact('student'));
    }
    
    /**
     * Met à jour un étudiant
     * 
     * @param Request $request
     * @param User $student
     * @return RedirectResponse
     */
    public function update(Request $request, User $student): RedirectResponse
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($student->id)],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date|before:today',
            'status' => ['required', Rule::in(['active', 'pending', 'suspended', 'banned'])],
            'avatar' => 'nullable|image|max:2048',
        ]);
        
        // Upload avatar
        if ($request->hasFile('avatar')) {
            if ($student->avatar) {
                Storage::disk('public')->delete($student->avatar);
            }
            $validated['avatar'] = $request->file('avatar')
                ->store('avatars', 'public');
        }
        
        $student->update($validated);
        
        return redirect()
            ->route('admin.students.show', $student)
            ->with('success', 'Étudiant mis à jour avec succès !');
    }
    
    /**
     * Affiche la progression d'un étudiant
     * 
     * @param User $student
     * @return View
     */
    public function progress(User $student): View
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        // Progression par formation
        $progressByFormation = [];
        
        $enrollments = Enrollment::where('user_id', $student->id)
            ->with('formation')
            ->get();
        
        foreach ($enrollments as $enrollment) {
            $progressByFormation[] = [
                'formation' => $enrollment->formation,
                'enrollment' => $enrollment,
                'progress' => $this->calculateFormationProgress($student, $enrollment->formation),
                'grades' => $this->getFormationGrades($student, $enrollment->formation),
                'attendance' => $this->getFormationAttendance($student, $enrollment->formation),
            ];
        }
        
        return view('admin.students.progress', compact('student', 'progressByFormation'));
    }
    
    /**
     * Affiche les notes d'un étudiant
     * 
     * @param User $student
     * @return View
     */
    public function grades(User $student): View
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $grades = Grade::where('user_id', $student->id)
            ->with(['subject', 'teacher', 'formation'])
            ->orderByDesc('created_at')
            ->paginate(25);
        
        // Statistiques des notes
        $gradesStats = [
            'average' => round(Grade::where('user_id', $student->id)->avg('grade') ?? 0, 1),
            'highest' => Grade::where('user_id', $student->id)->max('grade') ?? 0,
            'lowest' => Grade::where('user_id', $student->id)->min('grade') ?? 0,
            'total' => Grade::where('user_id', $student->id)->count(),
        ];
        
        return view('admin.students.grades', compact('student', 'grades', 'gradesStats'));
    }
    
    /**
     * Affiche l'assiduité d'un étudiant
     * 
     * @param User $student
     * @return View
     */
    public function attendance(User $student): View
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $attendances = Attendance::where('user_id', $student->id)
            ->with(['course.subject', 'course.teacher'])
            ->orderByDesc('created_at')
            ->paginate(25);
        
        // Statistiques de présence
        $attendanceStats = [
            'total' => Attendance::where('user_id', $student->id)->count(),
            'present' => Attendance::where('user_id', $student->id)->where('status', 'present')->count(),
            'absent' => Attendance::where('user_id', $student->id)->where('status', 'absent')->count(),
            'late' => Attendance::where('user_id', $student->id)->where('status', 'late')->count(),
            'rate' => 0,
        ];
        
        $attendanceStats['rate'] = $attendanceStats['total'] > 0 
            ? round(($attendanceStats['present'] / $attendanceStats['total']) * 100, 1) 
            : 0;
        
        return view('admin.students.attendance', compact('student', 'attendances', 'attendanceStats'));
    }
    
    /**
     * Affiche les paiements d'un étudiant
     * 
     * @param User $student
     * @return View
     */
    public function payments(User $student): View
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $payments = Payment::where('student_id', $student->id)
            ->with(['formation', 'enrollment'])
            ->orderByDesc('created_at')
            ->paginate(25);
        
        // Statistiques paiements
        $paymentsStats = [
            'total_paid' => Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->sum('amount'),
            'total_pending' => Payment::where('student_id', $student->id)
                ->where('status', 'pending')
                ->sum('amount'),
            'total_refunded' => Payment::where('student_id', $student->id)
                ->where('status', 'refunded')
                ->sum('refund_amount'),
            'total_transactions' => Payment::where('student_id', $student->id)->count(),
        ];
        
        return view('admin.students.payments', compact('student', 'payments', 'paymentsStats'));
    }
    
    /**
     * Inscrit manuellement un étudiant à une formation
     * 
     * @param Request $request
     * @param User $student
     * @return RedirectResponse
     */
    public function enroll(Request $request, User $student): RedirectResponse
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $validated = $request->validate([
            'formation_id' => 'required|exists:formations,id',
            'class_id' => 'nullable|exists:classes,id',
            'enrollment_date' => 'nullable|date',
        ]);
        
        // Vérifier si déjà inscrit
        $exists = Enrollment::where('user_id', $student->id)
            ->where('formation_id', $validated['formation_id'])
            ->whereIn('status', ['active', 'pending'])
            ->exists();
        
        if ($exists) {
            return redirect()
                ->back()
                ->with('warning', 'Cet étudiant est déjà inscrit à cette formation.');
        }
        
        // Créer l'inscription
        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'formation_id' => $validated['formation_id'],
            'class_id' => $validated['class_id'] ?? null,
            'enrollment_date' => $validated['enrollment_date'] ?? now(),
            'status' => 'active',
        ]);
        
        return redirect()
            ->route('admin.students.show', $student)
            ->with('success', 'Étudiant inscrit avec succès !');
    }
    
    /**
     * Annule une inscription
     * 
     * @param User $student
     * @param Enrollment $enrollment
     * @return RedirectResponse
     */
    public function cancelEnrollment(User $student, Enrollment $enrollment): RedirectResponse
    {
        if ($enrollment->user_id !== $student->id) {
            abort(403);
        }
        
        $enrollment->update(['status' => 'cancelled']);
        
        return redirect()
            ->back()
            ->with('success', 'Inscription annulée avec succès.');
    }
    
    /**
     * Réactive une inscription
     * 
     * @param User $student
     * @param Enrollment $enrollment
     * @return RedirectResponse
     */
    public function reactivateEnrollment(User $student, Enrollment $enrollment): RedirectResponse
    {
        if ($enrollment->user_id !== $student->id) {
            abort(403);
        }
        
        $enrollment->update(['status' => 'active']);
        
        return redirect()
            ->back()
            ->with('success', 'Inscription réactivée avec succès.');
    }
    
    /**
     * Suspend un étudiant
     * 
     * @param Request $request
     * @param User $student
     * @return RedirectResponse
     */
    public function suspend(Request $request, User $student): RedirectResponse
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        
        $student->update([
            'status' => 'suspended',
            'suspension_reason' => $validated['reason'],
            'suspended_at' => now(),
        ]);
        
        return redirect()
            ->back()
            ->with('success', 'Étudiant suspendu avec succès.');
    }
    
    /**
     * Réactive un étudiant
     * 
     * @param User $student
     * @return RedirectResponse
     */
    public function activate(User $student): RedirectResponse
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $student->update([
            'status' => 'active',
            'suspension_reason' => null,
            'suspended_at' => null,
        ]);
        
        return redirect()
            ->back()
            ->with('success', 'Étudiant réactivé avec succès !');
    }
    
    /**
     * Réinitialise le mot de passe d'un étudiant
     * 
     * @param User $student
     * @return RedirectResponse
     */
    public function resetPassword(User $student): RedirectResponse
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        // Générer un mot de passe temporaire
        $tempPassword = 'InfiniSchool' . rand(1000, 9999);
        
        $student->update([
            'password' => Hash::make($tempPassword),
            'password_changed_at' => null, // Forcer changement
        ]);
        
        // TODO: Envoyer email avec nouveau mot de passe
        // Mail::to($student->email)->send(new PasswordReset($tempPassword));
        
        return redirect()
            ->back()
            ->with('success', "Mot de passe réinitialisé : {$tempPassword}");
    }
    
    /**
     * Export des données d'un étudiant
     * 
     * @param User $student
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student-' . $student->id . '-' . date('Y-m-d') . '.csv"',
        ];
        
        $callback = function() use ($student) {
            $file = fopen('php://output', 'w');
            
            // Informations générales
            fputcsv($file, ['INFORMATIONS ÉTUDIANT']);
            fputcsv($file, ['Nom', $student->name]);
            fputcsv($file, ['Email', $student->email]);
            fputcsv($file, ['Téléphone', $student->phone]);
            fputcsv($file, ['Statut', $student->status]);
            fputcsv($file, ['Inscrit le', $student->created_at->format('Y-m-d')]);
            fputcsv($file, []);
            
            // Inscriptions
            fputcsv($file, ['INSCRIPTIONS']);
            fputcsv($file, ['Formation', 'Classe', 'Date', 'Statut']);
            foreach ($student->enrollments as $enrollment) {
                fputcsv($file, [
                    $enrollment->formation->name,
                    $enrollment->class->name ?? 'N/A',
                    $enrollment->enrollment_date->format('Y-m-d'),
                    $enrollment->status,
                ]);
            }
            fputcsv($file, []);
            
            // Notes
            fputcsv($file, ['NOTES']);
            fputcsv($file, ['Matière', 'Note', 'Date']);
            $grades = Grade::where('user_id', $student->id)->with('subject')->get();
            foreach ($grades as $grade) {
                fputcsv($file, [
                    $grade->subject->name,
                    $grade->grade,
                    $grade->created_at->format('Y-m-d'),
                ]);
            }
            fputcsv($file, []);
            
            // Paiements
            fputcsv($file, ['PAIEMENTS']);
            fputcsv($file, ['Formation', 'Montant', 'Date', 'Statut']);
            $payments = Payment::where('student_id', $student->id)->with('formation')->get();
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->formation->name,
                    $payment->amount . ' EUR',
                    $payment->created_at->format('Y-m-d'),
                    $payment->status,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Récupère les stats rapides d'un étudiant
     * 
     * @param User $student
     * @return array
     */
    private function getStudentQuickStats(User $student): array
    {
        return [
            'enrollments' => Enrollment::where('user_id', $student->id)->count(),
            'avg_grade' => round(Grade::where('user_id', $student->id)->avg('grade') ?? 0, 1),
            'attendance_rate' => $this->getAttendanceRate($student),
        ];
    }
    
    /**
     * Récupère les stats détaillées d'un étudiant
     * 
     * @param User $student
     * @return array
     */
    private function getStudentDetailedStats(User $student): array
    {
        // Inscriptions
        $totalEnrollments = Enrollment::where('user_id', $student->id)->count();
        $activeEnrollments = Enrollment::where('user_id', $student->id)
            ->where('status', 'active')
            ->count();
        $completedEnrollments = Enrollment::where('user_id', $student->id)
            ->where('status', 'completed')
            ->count();
        
        // Notes
        $grades = Grade::where('user_id', $student->id);
        $avgGrade = round($grades->avg('grade') ?? 0, 1);
        $totalGrades = $grades->count();
        
        // Assiduité
        $totalAttendances = Attendance::where('user_id', $student->id)->count();
        $presentAttendances = Attendance::where('user_id', $student->id)
            ->where('status', 'present')
            ->count();
        $attendanceRate = $totalAttendances > 0 
            ? round(($presentAttendances / $totalAttendances) * 100, 1) 
            : 0;
        
        // Paiements
        $totalPaid = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->sum('amount');
        $totalPending = Payment::where('student_id', $student->id)
            ->where('status', 'pending')
            ->sum('amount');
        
        // Certificats
        $certificatesCount = Certificate::where('user_id', $student->id)->count();
        
        return [
            'enrollments' => [
                'total' => $totalEnrollments,
                'active' => $activeEnrollments,
                'completed' => $completedEnrollments,
            ],
            'grades' => [
                'average' => $avgGrade,
                'total' => $totalGrades,
            ],
            'attendance' => [
                'total' => $totalAttendances,
                'present' => $presentAttendances,
                'rate' => $attendanceRate,
            ],
            'payments' => [
                'total_paid' => round($totalPaid, 2),
                'total_pending' => round($totalPending, 2),
            ],
            'certificates' => $certificatesCount,
            'member_since' => $student->created_at->diffForHumans(),
        ];
    }
    
    /**
     * Calcule la progression dans une formation
     * 
     * @param User $student
     * @param Formation $formation
     * @return float
     */
    private function calculateFormationProgress(User $student, Formation $formation): float
    {
        // Nombre de cours suivis vs total
        $totalCourses = Course::whereHas('class', function ($q) use ($formation) {
            $q->where('formation_id', $formation->id);
        })->count();
        
        $attendedCourses = Attendance::where('user_id', $student->id)
            ->where('status', 'present')
            ->whereHas('course.class', function ($q) use ($formation) {
                $q->where('formation_id', $formation->id);
            })
            ->count();
        
        return $totalCourses > 0 
            ? round(($attendedCourses / $totalCourses) * 100, 1) 
            : 0;
    }
    
    /**
     * Récupère les notes pour une formation
     * 
     * @param User $student
     * @param Formation $formation
     * @return array
     */
    private function getFormationGrades(User $student, Formation $formation): array
    {
        $grades = Grade::where('user_id', $student->id)
            ->where('formation_id', $formation->id)
            ->get();
        
        return [
            'average' => round($grades->avg('grade') ?? 0, 1),
            'count' => $grades->count(),
        ];
    }
    
    /**
     * Récupère l'assiduité pour une formation
     * 
     * @param User $student
     * @param Formation $formation
     * @return float
     */
    private function getFormationAttendance(User $student, Formation $formation): float
    {
        $total = Attendance::where('user_id', $student->id)
            ->whereHas('course.class', function ($q) use ($formation) {
                $q->where('formation_id', $formation->id);
            })
            ->count();
        
        $present = Attendance::where('user_id', $student->id)
            ->where('status', 'present')
            ->whereHas('course.class', function ($q) use ($formation) {
                $q->where('formation_id', $formation->id);
            })
            ->count();
        
        return $total > 0 ? round(($present / $total) * 100, 1) : 0;
    }
    
    /**
     * Récupère le taux d'assiduité global
     * 
     * @param User $student
     * @return float
     */
    private function getAttendanceRate(User $student): float
    {
        $total = Attendance::where('user_id', $student->id)->count();
        $present = Attendance::where('user_id', $student->id)
            ->where('status', 'present')
            ->count();
        
        return $total > 0 ? round(($present / $total) * 100, 1) : 0;
    }
}