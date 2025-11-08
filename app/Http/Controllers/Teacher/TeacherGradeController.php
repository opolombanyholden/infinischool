<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\ClassModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * TeacherGradeController
 * 
 * Gère les notes, évaluations et devoirs des étudiants
 * Permet la saisie, modification et export des notes
 * 
 * @package App\Http\Controllers\Teacher
 */
class TeacherGradeController extends Controller
{
    /**
     * Tableau de bord des notes
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $teacher = Auth::user();
        
        // Classes de l'enseignant
        $classes = ClassModel::whereHas('teachers', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->get();
        
        // Filtre par classe
        $selectedClass = $request->input('class_id');
        
        // Devoirs en attente de correction
        $pendingAssignments = Assignment::whereHas('course', function($query) use ($teacher, $selectedClass) {
                $query->where('teacher_id', $teacher->id);
                if ($selectedClass) {
                    $query->where('class_id', $selectedClass);
                }
            })
            ->where('due_date', '<', Carbon::now())
            ->whereDoesntHave('grades')
            ->with(['course.class', 'course.subject'])
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();
        
        // Statistiques
        $stats = [
            'pending_grades' => $this->getPendingGradesCount($teacher->id, $selectedClass),
            'graded_this_week' => $this->getGradedThisWeek($teacher->id, $selectedClass),
            'average_grade' => $this->getTeacherAverageGrade($teacher->id, $selectedClass),
            'total_assignments' => Assignment::whereHas('course', function($query) use ($teacher, $selectedClass) {
                $query->where('teacher_id', $teacher->id);
                if ($selectedClass) {
                    $query->where('class_id', $selectedClass);
                }
            })->count(),
        ];
        
        // Notes récentes
        $recentGrades = Grade::whereHas('assignment.course', function($query) use ($teacher, $selectedClass) {
                $query->where('teacher_id', $teacher->id);
                if ($selectedClass) {
                    $query->where('class_id', $selectedClass);
                }
            })
            ->with(['user', 'assignment.course.subject'])
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();
        
        return view('teacher.grades.index', compact(
            'classes',
            'selectedClass',
            'pendingAssignments',
            'stats',
            'recentGrades'
        ));
    }
    
    /**
     * Affiche la saisie de notes pour une classe
     * 
     * @param ClassModel $class
     * @param Request $request
     * @return View
     */
    public function classGrades(ClassModel $class, Request $request): View
    {
        $this->authorize('view', $class);
        
        $teacher = Auth::user();
        
        // Devoirs pour cette classe
        $assignments = Assignment::whereHas('course', function($query) use ($teacher, $class) {
                $query->where('teacher_id', $teacher->id)
                      ->where('class_id', $class->id);
            })
            ->with('course.subject')
            ->orderBy('due_date', 'desc')
            ->get();
        
        // Sélection d'un devoir spécifique
        $selectedAssignment = $request->input('assignment_id');
        
        // Étudiants de la classe avec leurs notes
        $students = $class->students()
            ->orderBy('last_name', 'asc')
            ->get()
            ->map(function($student) use ($assignments, $selectedAssignment) {
                // Toutes les notes de l'étudiant
                $student->grades = Grade::whereIn('assignment_id', $assignments->pluck('id'))
                    ->where('user_id', $student->id)
                    ->with('assignment')
                    ->get()
                    ->keyBy('assignment_id');
                
                // Moyenne de l'étudiant
                $student->average = $student->grades->avg('grade') ?? 0;
                
                // Note pour le devoir sélectionné
                if ($selectedAssignment) {
                    $student->selected_grade = $student->grades->get($selectedAssignment);
                }
                
                return $student;
            });
        
        return view('teacher.grades.class', compact(
            'class',
            'assignments',
            'students',
            'selectedAssignment'
        ));
    }
    
    /**
     * Affiche le formulaire de création de devoir
     * 
     * @return View
     */
    public function createAssignment(): View
    {
        $teacher = Auth::user();
        
        // Cours à venir
        $courses = Course::where('teacher_id', $teacher->id)
            ->where('scheduled_at', '>=', Carbon::now())
            ->with(['class', 'subject'])
            ->orderBy('scheduled_at', 'asc')
            ->get();
        
        return view('teacher.grades.create-assignment', compact('courses'));
    }
    
    /**
     * Crée un nouveau devoir
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeAssignment(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date|after:now',
            'max_grade' => 'required|numeric|min:0|max:100',
            'weight' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:homework,quiz,exam,project,presentation',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            $assignment = Assignment::create($request->all());
            
            return redirect()->route('teacher.grades.assignment.show', $assignment)
                ->with('success', 'Devoir créé avec succès !');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Affiche les détails d'un devoir
     * 
     * @param Assignment $assignment
     * @return View
     */
    public function showAssignment(Assignment $assignment): View
    {
        $this->authorize('view', $assignment);
        
        $assignment->load(['course.class.students', 'course.subject', 'grades.user']);
        
        // Statistiques du devoir
        $stats = [
            'total_students' => $assignment->course->class->students->count(),
            'submitted' => $assignment->grades->count(),
            'pending' => $assignment->course->class->students->count() - $assignment->grades->count(),
            'average' => $assignment->grades->avg('grade') ?? 0,
            'highest' => $assignment->grades->max('grade') ?? 0,
            'lowest' => $assignment->grades->min('grade') ?? 0,
        ];
        
        // Distribution des notes
        $distribution = $this->getGradeDistribution($assignment->id);
        
        return view('teacher.grades.assignment-detail', compact('assignment', 'stats', 'distribution'));
    }
    
    /**
     * Saisie rapide d'une note (AJAX)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function storeGrade(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|exists:assignments,id',
            'user_id' => 'required|exists:users,id',
            'grade' => 'required|numeric|min:0|max:100',
            'comment' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            // Vérifier que l'enseignant a accès
            $assignment = Assignment::with('course')->findOrFail($request->input('assignment_id'));
            
            if ($assignment->course->teacher_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé.',
                ], 403);
            }
            
            // Créer ou mettre à jour la note
            $grade = Grade::updateOrCreate(
                [
                    'assignment_id' => $request->input('assignment_id'),
                    'user_id' => $request->input('user_id'),
                ],
                [
                    'grade' => $request->input('grade'),
                    'comment' => $request->input('comment'),
                    'graded_by' => Auth::id(),
                    'graded_at' => Carbon::now(),
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Note enregistrée avec succès !',
                'grade' => $grade,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Saisie multiple de notes (AJAX)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function storeMultipleGrades(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|exists:assignments,id',
            'grades' => 'required|array',
            'grades.*.user_id' => 'required|exists:users,id',
            'grades.*.grade' => 'required|numeric|min:0|max:100',
            'grades.*.comment' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $assignment = Assignment::with('course')->findOrFail($request->input('assignment_id'));
            
            if ($assignment->course->teacher_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé.',
                ], 403);
            }
            
            DB::beginTransaction();
            
            $saved = 0;
            foreach ($request->input('grades') as $gradeData) {
                Grade::updateOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'user_id' => $gradeData['user_id'],
                    ],
                    [
                        'grade' => $gradeData['grade'],
                        'comment' => $gradeData['comment'] ?? null,
                        'graded_by' => Auth::id(),
                        'graded_at' => Carbon::now(),
                    ]
                );
                $saved++;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "{$saved} note(s) enregistrée(s) avec succès !",
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Met à jour une note
     * 
     * @param Request $request
     * @param Grade $grade
     * @return JsonResponse
     */
    public function updateGrade(Request $request, Grade $grade): JsonResponse
    {
        $this->authorize('update', $grade);
        
        $validator = Validator::make($request->all(), [
            'grade' => 'required|numeric|min:0|max:100',
            'comment' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $grade->update([
                'grade' => $request->input('grade'),
                'comment' => $request->input('comment'),
                'graded_at' => Carbon::now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Note mise à jour avec succès !',
                'grade' => $grade->fresh(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Supprime une note
     * 
     * @param Grade $grade
     * @return JsonResponse
     */
    public function destroyGrade(Grade $grade): JsonResponse
    {
        $this->authorize('delete', $grade);
        
        try {
            $grade->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Note supprimée avec succès !',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Export des notes (Excel/PDF)
     * 
     * @param Request $request
     * @param ClassModel $class
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportGrades(Request $request, ClassModel $class)
    {
        $this->authorize('view', $class);
        
        $format = $request->input('format', 'excel'); // excel, pdf, csv
        $teacher = Auth::user();
        
        // Récupérer les données
        $assignments = Assignment::whereHas('course', function($query) use ($teacher, $class) {
                $query->where('teacher_id', $teacher->id)
                      ->where('class_id', $class->id);
            })
            ->with('course.subject')
            ->get();
        
        $students = $class->students()
            ->orderBy('last_name', 'asc')
            ->get();
        
        $data = $students->map(function($student) use ($assignments) {
            $row = [
                'Nom' => $student->full_name,
                'Email' => $student->email,
            ];
            
            foreach ($assignments as $assignment) {
                $grade = Grade::where('assignment_id', $assignment->id)
                    ->where('user_id', $student->id)
                    ->first();
                
                $row[$assignment->title] = $grade ? $grade->grade : 'N/A';
            }
            
            // Moyenne
            $grades = Grade::whereIn('assignment_id', $assignments->pluck('id'))
                ->where('user_id', $student->id)
                ->pluck('grade');
            
            $row['Moyenne'] = $grades->count() > 0 ? round($grades->avg(), 2) : 'N/A';
            
            return $row;
        });
        
        // Générer le fichier selon le format
        switch ($format) {
            case 'pdf':
                return $this->generateGradesPDF($data, $class);
            case 'csv':
                return $this->generateGradesCSV($data, $class);
            case 'excel':
            default:
                return $this->generateGradesExcel($data, $class);
        }
    }
    
    /**
     * Statistiques de notes par matière
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        $teacher = Auth::user();
        $classId = $request->input('class_id');
        $period = $request->input('period', '30days'); // 7days, 30days, 3months, year
        
        $startDate = match($period) {
            '7days' => Carbon::now()->subDays(7),
            '30days' => Carbon::now()->subDays(30),
            '3months' => Carbon::now()->subMonths(3),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(30),
        };
        
        // Statistiques par matière
        $subjectStats = DB::table('grades')
            ->join('assignments', 'grades.assignment_id', '=', 'assignments.id')
            ->join('courses', 'assignments.course_id', '=', 'courses.id')
            ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
            ->where('courses.teacher_id', $teacher->id)
            ->when($classId, fn($q) => $q->where('courses.class_id', $classId))
            ->where('grades.created_at', '>=', $startDate)
            ->select([
                'subjects.name as subject',
                DB::raw('AVG(grades.grade) as average'),
                DB::raw('COUNT(*) as count'),
                DB::raw('MIN(grades.grade) as min'),
                DB::raw('MAX(grades.grade) as max'),
            ])
            ->groupBy('subjects.id', 'subjects.name')
            ->get();
        
        return response()->json([
            'subjects' => $subjectStats,
            'period' => $period,
        ]);
    }
    
    /**
     * Compte les notes en attente
     * 
     * @param int $teacherId
     * @param int|null $classId
     * @return int
     */
    private function getPendingGradesCount(int $teacherId, ?int $classId = null): int
    {
        return Assignment::whereHas('course', function($query) use ($teacherId, $classId) {
                $query->where('teacher_id', $teacherId);
                if ($classId) {
                    $query->where('class_id', $classId);
                }
            })
            ->where('due_date', '<', Carbon::now())
            ->whereDoesntHave('grades')
            ->count();
    }
    
    /**
     * Compte les notes saisies cette semaine
     * 
     * @param int $teacherId
     * @param int|null $classId
     * @return int
     */
    private function getGradedThisWeek(int $teacherId, ?int $classId = null): int
    {
        return Grade::whereHas('assignment.course', function($query) use ($teacherId, $classId) {
                $query->where('teacher_id', $teacherId);
                if ($classId) {
                    $query->where('class_id', $classId);
                }
            })
            ->where('graded_at', '>=', Carbon::now()->startOfWeek())
            ->count();
    }
    
    /**
     * Calcule la moyenne générale de l'enseignant
     * 
     * @param int $teacherId
     * @param int|null $classId
     * @return float
     */
    private function getTeacherAverageGrade(int $teacherId, ?int $classId = null): float
    {
        $average = Grade::whereHas('assignment.course', function($query) use ($teacherId, $classId) {
                $query->where('teacher_id', $teacherId);
                if ($classId) {
                    $query->where('class_id', $classId);
                }
            })
            ->avg('grade');
        
        return round($average ?? 0, 2);
    }
    
    /**
     * Obtient la distribution des notes pour un devoir
     * 
     * @param int $assignmentId
     * @return array
     */
    private function getGradeDistribution(int $assignmentId): array
    {
        $grades = Grade::where('assignment_id', $assignmentId)->pluck('grade');
        
        return [
            '0-10' => $grades->filter(fn($g) => $g < 10)->count(),
            '10-20' => $grades->filter(fn($g) => $g >= 10 && $g < 20)->count(),
            '20-30' => $grades->filter(fn($g) => $g >= 20 && $g < 30)->count(),
            '30-40' => $grades->filter(fn($g) => $g >= 30 && $g < 40)->count(),
            '40-50' => $grades->filter(fn($g) => $g >= 40 && $g < 50)->count(),
            '50-60' => $grades->filter(fn($g) => $g >= 50 && $g < 60)->count(),
            '60-70' => $grades->filter(fn($g) => $g >= 60 && $g < 70)->count(),
            '70-80' => $grades->filter(fn($g) => $g >= 70 && $g < 80)->count(),
            '80-90' => $grades->filter(fn($g) => $g >= 80 && $g < 90)->count(),
            '90-100' => $grades->filter(fn($g) => $g >= 90)->count(),
        ];
    }
    
    /**
     * Génère un fichier Excel
     */
    private function generateGradesExcel($data, ClassModel $class)
    {
        // À implémenter avec Laravel Excel
    }
    
    /**
     * Génère un fichier CSV
     */
    private function generateGradesCSV($data, ClassModel $class)
    {
        // À implémenter
    }
    
    /**
     * Génère un fichier PDF
     */
    private function generateGradesPDF($data, ClassModel $class)
    {
        // À implémenter avec DomPDF
    }
}