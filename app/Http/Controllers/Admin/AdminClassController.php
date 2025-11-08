<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Formation;
use App\Models\User;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * AdminClassController
 * 
 * Gère l'organisation et la répartition des classes
 * Création auto/manuelle, assignation enseignants, emplois du temps
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminClassController extends Controller
{
    /**
     * Affiche la liste des classes avec filtres
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = ClassModel::with(['formation', 'students']);
        
        // Filtre par formation
        if ($request->filled('formation_id')) {
            $query->where('formation_id', $request->formation_id);
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtre par année académique
        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }
        
        // Filtre classes surchargées
        if ($request->input('filter') === 'overloaded') {
            $query->withCount('students')
                ->having('students_count', '>', 30);
        }
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        // Ajouter les compteurs
        $query->withCount(['students', 'courses', 'schedules']);
        
        // Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Pagination
        $perPage = $request->input('per_page', 20);
        $classes = $query->paginate($perPage)->withQueryString();
        
        // Statistiques globales
        $stats = [
            'total' => ClassModel::count(),
            'active' => ClassModel::where('status', 'active')->count(),
            'archived' => ClassModel::where('status', 'archived')->count(),
            'total_students' => Enrollment::where('status', 'active')->count(),
            'avg_students_per_class' => ClassModel::withCount('students')->avg('students_count') ?? 0,
            'overloaded_classes' => ClassModel::withCount('students')
                ->having('students_count', '>', 30)
                ->count(),
        ];
        
        // Formations pour le filtre
        $formations = Formation::where('status', 'active')
            ->orderBy('name')
            ->get();
        
        return view('admin.classes.index', compact('classes', 'stats', 'formations'));
    }
    
    /**
     * Affiche le formulaire de création de classe
     * 
     * @return View
     */
    public function create(): View
    {
        $formations = Formation::where('status', 'active')
            ->orderBy('name')
            ->get();
        
        $currentYear = date('Y');
        $academicYears = [
            $currentYear . '-' . ($currentYear + 1),
            ($currentYear - 1) . '-' . $currentYear,
            ($currentYear + 1) . '-' . ($currentYear + 2),
        ];
        
        return view('admin.classes.create', compact('formations', 'academicYears'));
    }
    
    /**
     * Enregistre une nouvelle classe
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:classes,code',
            'formation_id' => 'required|exists:formations,id',
            'academic_year' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'max_students' => 'required|integer|min:1|max:50',
            'status' => ['required', Rule::in(['active', 'pending', 'completed', 'archived'])],
            'description' => 'nullable|string|max:1000',
        ]);
        
        $class = ClassModel::create($validated);
        
        return redirect()
            ->route('admin.classes.show', $class)
            ->with('success', 'Classe créée avec succès !');
    }
    
    /**
     * Affiche les détails d'une classe
     * 
     * @param ClassModel $class
     * @return View
     */
    public function show(ClassModel $class): View
    {
        // Charger les relations
        $class->load([
            'formation',
            'students' => function ($query) {
                $query->orderBy('name');
            },
            'teachers',
            'schedules',
            'courses' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);
        
        // Statistiques de la classe
        $stats = $this->getClassStats($class);
        
        // Enseignants disponibles pour assignation
        $availableTeachers = User::where('role', 'teacher')
            ->where('status', 'active')
            ->whereNotIn('id', $class->teachers->pluck('id'))
            ->get();
        
        // Matières de la formation
        $subjects = Subject::where('formation_id', $class->formation_id)
            ->orderBy('order')
            ->get();
        
        // Étudiants non assignés à cette formation
        $availableStudents = User::where('role', 'student')
            ->where('status', 'active')
            ->whereHas('enrollments', function ($query) use ($class) {
                $query->where('formation_id', $class->formation_id)
                    ->where('status', 'active')
                    ->whereNull('class_id');
            })
            ->get();
        
        return view('admin.classes.show', compact(
            'class',
            'stats',
            'availableTeachers',
            'subjects',
            'availableStudents'
        ));
    }
    
    /**
     * Affiche le formulaire d'édition de classe
     * 
     * @param ClassModel $class
     * @return View
     */
    public function edit(ClassModel $class): View
    {
        $formations = Formation::where('status', 'active')
            ->orderBy('name')
            ->get();
        
        $currentYear = date('Y');
        $academicYears = [
            $currentYear . '-' . ($currentYear + 1),
            ($currentYear - 1) . '-' . $currentYear,
            ($currentYear + 1) . '-' . ($currentYear + 2),
        ];
        
        return view('admin.classes.edit', compact('class', 'formations', 'academicYears'));
    }
    
    /**
     * Met à jour une classe
     * 
     * @param Request $request
     * @param ClassModel $class
     * @return RedirectResponse
     */
    public function update(Request $request, ClassModel $class): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('classes')->ignore($class->id)],
            'formation_id' => 'required|exists:formations,id',
            'academic_year' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'max_students' => 'required|integer|min:1|max:50',
            'status' => ['required', Rule::in(['active', 'pending', 'completed', 'archived'])],
            'description' => 'nullable|string|max:1000',
        ]);
        
        $class->update($validated);
        
        return redirect()
            ->route('admin.classes.show', $class)
            ->with('success', 'Classe mise à jour avec succès !');
    }
    
    /**
     * Supprime une classe
     * 
     * @param ClassModel $class
     * @return RedirectResponse
     */
    public function destroy(ClassModel $class): RedirectResponse
    {
        // Vérifier qu'il n'y a pas d'étudiants
        if ($class->students()->count() > 0) {
            return redirect()
                ->route('admin.classes.show', $class)
                ->with('error', 'Impossible de supprimer : la classe contient des étudiants.');
        }
        
        // Vérifier qu'il n'y a pas de cours programmés
        $upcomingCourses = Course::where('class_id', $class->id)
            ->where('status', 'scheduled')
            ->count();
        
        if ($upcomingCourses > 0) {
            return redirect()
                ->route('admin.classes.show', $class)
                ->with('error', "Impossible de supprimer : {$upcomingCourses} cours programmés.");
        }
        
        DB::beginTransaction();
        
        try {
            // Détacher les enseignants
            $class->teachers()->detach();
            
            // Supprimer les emplois du temps
            Schedule::where('class_id', $class->id)->delete();
            
            // Supprimer la classe
            $class->delete();
            
            DB::commit();
            
            return redirect()
                ->route('admin.classes.index')
                ->with('success', 'Classe supprimée avec succès !');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
    
    /**
     * Répartition automatique des étudiants
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function autoAssignStudents(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'formation_id' => 'required|exists:formations,id',
            'max_per_class' => 'integer|min:1|max:50',
        ]);
        
        $formationId = $validated['formation_id'];
        $maxPerClass = $validated['max_per_class'] ?? 30;
        
        DB::beginTransaction();
        
        try {
            // Récupérer les étudiants non assignés pour cette formation
            $unassignedStudents = User::where('role', 'student')
                ->where('status', 'active')
                ->whereHas('enrollments', function ($query) use ($formationId) {
                    $query->where('formation_id', $formationId)
                        ->where('status', 'active')
                        ->whereNull('class_id');
                })
                ->get();
            
            if ($unassignedStudents->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun étudiant non assigné trouvé.',
                ], 400);
            }
            
            // Récupérer les classes actives de cette formation
            $classes = ClassModel::where('formation_id', $formationId)
                ->where('status', 'active')
                ->withCount('students')
                ->get()
                ->filter(function ($class) use ($maxPerClass) {
                    return $class->students_count < $maxPerClass;
                });
            
            // Calculer combien de nouvelles classes sont nécessaires
            $availableSlots = $classes->sum(function ($class) use ($maxPerClass) {
                return $maxPerClass - $class->students_count;
            });
            
            $studentsCount = $unassignedStudents->count();
            
            if ($availableSlots < $studentsCount) {
                $neededClasses = ceil(($studentsCount - $availableSlots) / $maxPerClass);
                
                // Créer les classes manquantes
                $formation = Formation::find($formationId);
                $currentYear = date('Y');
                $academicYear = $currentYear . '-' . ($currentYear + 1);
                
                for ($i = 1; $i <= $neededClasses; $i++) {
                    $classNumber = ClassModel::where('formation_id', $formationId)->count() + 1;
                    
                    $newClass = ClassModel::create([
                        'name' => $formation->name . ' - Classe ' . $classNumber,
                        'code' => $formation->code . '-C' . $classNumber,
                        'formation_id' => $formationId,
                        'academic_year' => $academicYear,
                        'start_date' => now(),
                        'end_date' => now()->addWeeks($formation->duration_weeks),
                        'max_students' => $maxPerClass,
                        'status' => 'active',
                    ]);
                    
                    $classes->push($newClass);
                }
            }
            
            // Répartir les étudiants
            $classIndex = 0;
            $assignedCount = 0;
            
            foreach ($unassignedStudents as $student) {
                // Trouver la classe avec le moins d'étudiants
                $targetClass = $classes->sortBy('students_count')->first();
                
                // Assigner l'étudiant
                Enrollment::where('user_id', $student->id)
                    ->where('formation_id', $formationId)
                    ->where('status', 'active')
                    ->update(['class_id' => $targetClass->id]);
                
                // Incrémenter le compteur de la classe
                $targetClass->students_count++;
                $assignedCount++;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "{$assignedCount} étudiant(s) assigné(s) automatiquement.",
                'assigned_count' => $assignedCount,
                'classes_created' => $neededClasses ?? 0,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation : ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Assigne un enseignant à une classe pour une matière
     * 
     * @param Request $request
     * @param ClassModel $class
     * @return RedirectResponse
     */
    public function assignTeacher(Request $request, ClassModel $class): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);
        
        // Vérifier que l'enseignant est bien un enseignant
        $teacher = User::findOrFail($validated['teacher_id']);
        if ($teacher->role !== 'teacher') {
            return redirect()
                ->back()
                ->with('error', 'L\'utilisateur sélectionné n\'est pas un enseignant.');
        }
        
        // Vérifier que la matière appartient à la formation de la classe
        $subject = Subject::findOrFail($validated['subject_id']);
        if ($subject->formation_id !== $class->formation_id) {
            return redirect()
                ->back()
                ->with('error', 'Cette matière n\'appartient pas à la formation de la classe.');
        }
        
        // Assigner l'enseignant (éviter les doublons)
        $exists = DB::table('class_teacher')
            ->where('class_id', $class->id)
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $subject->id)
            ->exists();
        
        if ($exists) {
            return redirect()
                ->back()
                ->with('warning', 'Cet enseignant est déjà assigné à cette matière pour cette classe.');
        }
        
        DB::table('class_teacher')->insert([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()
            ->back()
            ->with('success', "Enseignant {$teacher->name} assigné à {$subject->name} avec succès !");
    }
    
    /**
     * Retire un enseignant d'une classe
     * 
     * @param Request $request
     * @param ClassModel $class
     * @return RedirectResponse
     */
    public function removeTeacher(Request $request, ClassModel $class): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);
        
        $query = DB::table('class_teacher')
            ->where('class_id', $class->id)
            ->where('teacher_id', $validated['teacher_id']);
        
        if (isset($validated['subject_id'])) {
            $query->where('subject_id', $validated['subject_id']);
        }
        
        $deleted = $query->delete();
        
        if ($deleted) {
            return redirect()
                ->back()
                ->with('success', 'Enseignant retiré avec succès !');
        }
        
        return redirect()
            ->back()
            ->with('error', 'Assignation non trouvée.');
    }
    
    /**
     * Ajoute un étudiant à une classe
     * 
     * @param Request $request
     * @param ClassModel $class
     * @return RedirectResponse
     */
    public function addStudent(Request $request, ClassModel $class): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);
        
        $student = User::findOrFail($validated['student_id']);
        
        // Vérifier que c'est bien un étudiant
        if ($student->role !== 'student') {
            return redirect()
                ->back()
                ->with('error', 'L\'utilisateur sélectionné n\'est pas un étudiant.');
        }
        
        // Vérifier la capacité de la classe
        if ($class->students()->count() >= $class->max_students) {
            return redirect()
                ->back()
                ->with('error', 'La classe a atteint sa capacité maximale.');
        }
        
        // Vérifier que l'étudiant est inscrit à cette formation
        $enrollment = Enrollment::where('user_id', $student->id)
            ->where('formation_id', $class->formation_id)
            ->where('status', 'active')
            ->first();
        
        if (!$enrollment) {
            return redirect()
                ->back()
                ->with('error', 'L\'étudiant n\'est pas inscrit à cette formation.');
        }
        
        // Assigner à la classe
        $enrollment->update(['class_id' => $class->id]);
        
        return redirect()
            ->back()
            ->with('success', "Étudiant {$student->name} ajouté à la classe avec succès !");
    }
    
    /**
     * Retire un étudiant d'une classe
     * 
     * @param Request $request
     * @param ClassModel $class
     * @return RedirectResponse
     */
    public function removeStudent(Request $request, ClassModel $class): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);
        
        $enrollment = Enrollment::where('user_id', $validated['student_id'])
            ->where('class_id', $class->id)
            ->first();
        
        if (!$enrollment) {
            return redirect()
                ->back()
                ->with('error', 'Étudiant non trouvé dans cette classe.');
        }
        
        $enrollment->update(['class_id' => null]);
        
        return redirect()
            ->back()
            ->with('success', 'Étudiant retiré de la classe avec succès !');
    }
    
    /**
     * Transfère un étudiant vers une autre classe
     * 
     * @param Request $request
     * @param ClassModel $class
     * @return RedirectResponse
     */
    public function transferStudent(Request $request, ClassModel $class): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'target_class_id' => 'required|exists:classes,id',
        ]);
        
        $targetClass = ClassModel::findOrFail($validated['target_class_id']);
        
        // Vérifier que les classes sont de la même formation
        if ($class->formation_id !== $targetClass->formation_id) {
            return redirect()
                ->back()
                ->with('error', 'Les classes doivent appartenir à la même formation.');
        }
        
        // Vérifier la capacité de la classe cible
        if ($targetClass->students()->count() >= $targetClass->max_students) {
            return redirect()
                ->back()
                ->with('error', 'La classe cible a atteint sa capacité maximale.');
        }
        
        // Effectuer le transfert
        $enrollment = Enrollment::where('user_id', $validated['student_id'])
            ->where('class_id', $class->id)
            ->first();
        
        if (!$enrollment) {
            return redirect()
                ->back()
                ->with('error', 'Étudiant non trouvé dans cette classe.');
        }
        
        $enrollment->update(['class_id' => $targetClass->id]);
        
        return redirect()
            ->back()
            ->with('success', "Étudiant transféré vers {$targetClass->name} avec succès !");
    }
    
    /**
     * Génère l'emploi du temps de la classe
     * 
     * @param ClassModel $class
     * @return View
     */
    public function schedule(ClassModel $class): View
    {
        $schedules = Schedule::where('class_id', $class->id)
            ->with(['subject', 'teacher'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        
        $subjects = Subject::where('formation_id', $class->formation_id)->get();
        $teachers = $class->teachers;
        
        return view('admin.classes.schedule', compact('class', 'schedules', 'subjects', 'teachers'));
    }
    
    /**
     * Récupère les statistiques d'une classe
     * 
     * @param ClassModel $class
     * @return array
     */
    private function getClassStats(ClassModel $class): array
    {
        // Nombre d'étudiants
        $studentsCount = $class->students()->count();
        $capacityRate = $class->max_students > 0 
            ? ($studentsCount / $class->max_students) * 100 
            : 0;
        
        // Cours
        $totalCourses = Course::where('class_id', $class->id)->count();
        $completedCourses = Course::where('class_id', $class->id)
            ->where('status', 'completed')
            ->count();
        $upcomingCourses = Course::where('class_id', $class->id)
            ->where('status', 'scheduled')
            ->where('start_time', '>', now())
            ->count();
        
        // Taux d'assiduité
        $totalAttendances = Attendance::whereHas('course', function ($query) use ($class) {
            $query->where('class_id', $class->id);
        })->count();
        
        $presentAttendances = Attendance::where('status', 'present')
            ->whereHas('course', function ($query) use ($class) {
                $query->where('class_id', $class->id);
            })->count();
        
        $attendanceRate = $totalAttendances > 0 
            ? ($presentAttendances / $totalAttendances) * 100 
            : 0;
        
        // Note moyenne de la classe
        $avgGrade = DB::table('grades')
            ->join('enrollments', 'grades.user_id', '=', 'enrollments.user_id')
            ->where('enrollments.class_id', $class->id)
            ->avg('grades.grade') ?? 0;
        
        // Enseignants
        $teachersCount = $class->teachers()->count();
        
        return [
            'students' => [
                'count' => $studentsCount,
                'max' => $class->max_students,
                'capacity_rate' => round($capacityRate, 1),
                'available_slots' => $class->max_students - $studentsCount,
            ],
            'courses' => [
                'total' => $totalCourses,
                'completed' => $completedCourses,
                'upcoming' => $upcomingCourses,
            ],
            'attendance_rate' => round($attendanceRate, 1),
            'avg_grade' => round($avgGrade, 1),
            'teachers_count' => $teachersCount,
        ];
    }
}