<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\Course;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * TeacherClassController
 * 
 * Gère les classes assignées à l'enseignant
 * Affiche les listes d'étudiants, statistiques et performances
 * 
 * @package App\Http\Controllers\Teacher
 */
class TeacherClassController extends Controller
{
    /**
     * Liste des classes de l'enseignant
     * 
     * @return View
     */
    public function index(): View
    {
        $teacher = Auth::user();
        
        $classes = ClassModel::whereHas('teachers', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->withCount(['students', 'courses'])
            ->with(['formation', 'subjects'])
            ->get();
        
        // Ajouter des statistiques personnalisées pour chaque classe
        $classes->each(function($class) use ($teacher) {
            // Taux de présence moyen
            $class->avg_attendance = $this->getClassAttendanceRate($class->id, $teacher->id);
            
            // Note moyenne
            $class->avg_grade = $this->getClassAverageGrade($class->id, $teacher->id);
            
            // Prochains cours
            $class->upcoming_courses = Course::where('class_id', $class->id)
                ->where('teacher_id', $teacher->id)
                ->where('status', 'scheduled')
                ->where('scheduled_at', '>', Carbon::now())
                ->count();
            
            // Devoirs en attente de correction
            $class->pending_grades = $this->getPendingGradesCount($class->id, $teacher->id);
        });
        
        return view('teacher.classes.index', compact('classes'));
    }
    
    /**
     * Affiche les détails d'une classe
     * 
     * @param ClassModel $class
     * @return View
     */
    public function show(ClassModel $class): View
    {
        // Vérifier que l'enseignant a accès à cette classe
        $this->authorize('view', $class);
        
        $teacher = Auth::user();
        
        // Charger les relations
        $class->load([
            'formation',
            'subjects',
            'students' => function($query) {
                $query->orderBy('last_name', 'asc');
            },
        ]);
        
        // Statistiques détaillées
        $stats = [
            'total_students' => $class->students->count(),
            'active_students' => $class->students->where('status', 'active')->count(),
            'total_courses' => Course::where('class_id', $class->id)
                ->where('teacher_id', $teacher->id)
                ->count(),
            'completed_courses' => Course::where('class_id', $class->id)
                ->where('teacher_id', $teacher->id)
                ->where('status', 'completed')
                ->count(),
            'avg_attendance' => $this->getClassAttendanceRate($class->id, $teacher->id),
            'avg_grade' => $this->getClassAverageGrade($class->id, $teacher->id),
        ];
        
        // Liste des étudiants avec leurs stats individuelles
        $students = $class->students->map(function($student) use ($class, $teacher) {
            $student->attendance_rate = $this->getStudentAttendanceRate(
                $student->id, 
                $class->id, 
                $teacher->id
            );
            
            $student->avg_grade = $this->getStudentAverageGrade(
                $student->id, 
                $class->id, 
                $teacher->id
            );
            
            $student->courses_attended = DB::table('attendances')
                ->join('courses', 'attendances.course_id', '=', 'courses.id')
                ->where('attendances.user_id', $student->id)
                ->where('courses.class_id', $class->id)
                ->where('courses.teacher_id', $teacher->id)
                ->where('attendances.status', 'present')
                ->count();
            
            return $student;
        });
        
        // Cours récents et à venir
        $recentCourses = Course::where('class_id', $class->id)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->with('subject')
            ->orderBy('scheduled_at', 'desc')
            ->limit(5)
            ->get();
        
        $upcomingCourses = Course::where('class_id', $class->id)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'scheduled')
            ->with('subject')
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->get();
        
        return view('teacher.classes.show', compact(
            'class',
            'stats',
            'students',
            'recentCourses',
            'upcomingCourses'
        ));
    }
    
    /**
     * Affiche les détails d'un étudiant dans une classe
     * 
     * @param ClassModel $class
     * @param User $student
     * @return View
     */
    public function showStudent(ClassModel $class, User $student): View
    {
        $this->authorize('view', $class);
        
        // Vérifier que l'étudiant est bien dans cette classe
        if (!$class->students->contains($student)) {
            abort(404, 'Étudiant non trouvé dans cette classe.');
        }
        
        $teacher = Auth::user();
        
        // Profil étudiant avec détails
        $studentProfile = [
            'basic_info' => $student->only(['first_name', 'last_name', 'email', 'phone']),
            'enrollment_date' => DB::table('enrollments')
                ->where('user_id', $student->id)
                ->where('class_id', $class->id)
                ->value('created_at'),
            'status' => $student->status,
        ];
        
        // Statistiques de présence
        $attendanceStats = $this->getStudentAttendanceDetails($student->id, $class->id, $teacher->id);
        
        // Historique des cours
        $courseHistory = DB::table('courses')
            ->leftJoin('attendances', function($join) use ($student) {
                $join->on('courses.id', '=', 'attendances.course_id')
                     ->where('attendances.user_id', $student->id);
            })
            ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
            ->where('courses.class_id', $class->id)
            ->where('courses.teacher_id', $teacher->id)
            ->select([
                'courses.*',
                'subjects.name as subject_name',
                'attendances.status as attendance_status',
                'attendances.checked_in_at'
            ])
            ->orderBy('courses.scheduled_at', 'desc')
            ->get();
        
        // Notes et évaluations
        $grades = Grade::whereHas('assignment.course', function($query) use ($class, $teacher) {
                $query->where('class_id', $class->id)
                      ->where('teacher_id', $teacher->id);
            })
            ->where('user_id', $student->id)
            ->with(['assignment.course.subject'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Graphique de progression
        $progressionData = $this->getStudentProgressionData($student->id, $class->id, $teacher->id);
        
        return view('teacher.classes.student-detail', compact(
            'class',
            'student',
            'studentProfile',
            'attendanceStats',
            'courseHistory',
            'grades',
            'progressionData'
        ));
    }
    
    /**
     * Exporte la liste des étudiants (Excel/PDF)
     * 
     * @param Request $request
     * @param ClassModel $class
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportStudents(Request $request, ClassModel $class)
    {
        $this->authorize('view', $class);
        
        $format = $request->input('format', 'excel'); // excel, pdf, csv
        
        $teacher = Auth::user();
        
        // Récupérer les données
        $students = $class->students->map(function($student) use ($class, $teacher) {
            return [
                'Nom complet' => $student->full_name,
                'Email' => $student->email,
                'Téléphone' => $student->phone ?? 'N/A',
                'Taux de présence' => $this->getStudentAttendanceRate($student->id, $class->id, $teacher->id) . '%',
                'Moyenne' => number_format($this->getStudentAverageGrade($student->id, $class->id, $teacher->id), 2),
                'Statut' => $student->status,
            ];
        });
        
        // Générer le fichier selon le format
        switch ($format) {
            case 'pdf':
                return $this->generatePDF($students, $class);
            case 'csv':
                return $this->generateCSV($students, $class);
            case 'excel':
            default:
                return $this->generateExcel($students, $class);
        }
    }
    
    /**
     * Envoie un message à toute la classe
     * 
     * @param Request $request
     * @param ClassModel $class
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request, ClassModel $class)
    {
        $this->authorize('view', $class);
        
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        
        $teacher = Auth::user();
        
        // Envoyer le message à tous les étudiants
        foreach ($class->students as $student) {
            DB::table('messages')->insert([
                'sender_id' => $teacher->id,
                'receiver_id' => $student->id,
                'subject' => $request->input('subject'),
                'body' => $request->input('message'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Message envoyé à ' . $class->students->count() . ' étudiant(s).',
        ]);
    }
    
    /**
     * Obtient le taux de présence d'une classe
     * 
     * @param int $classId
     * @param int $teacherId
     * @return float
     */
    private function getClassAttendanceRate(int $classId, int $teacherId): float
    {
        $total = DB::table('attendances')
            ->join('courses', 'attendances.course_id', '=', 'courses.id')
            ->where('courses.class_id', $classId)
            ->where('courses.teacher_id', $teacherId)
            ->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $present = DB::table('attendances')
            ->join('courses', 'attendances.course_id', '=', 'courses.id')
            ->where('courses.class_id', $classId)
            ->where('courses.teacher_id', $teacherId)
            ->where('attendances.status', 'present')
            ->count();
        
        return round(($present / $total) * 100, 1);
    }
    
    /**
     * Obtient la note moyenne d'une classe
     * 
     * @param int $classId
     * @param int $teacherId
     * @return float
     */
    private function getClassAverageGrade(int $classId, int $teacherId): float
    {
        $average = DB::table('grades')
            ->join('assignments', 'grades.assignment_id', '=', 'assignments.id')
            ->join('courses', 'assignments.course_id', '=', 'courses.id')
            ->where('courses.class_id', $classId)
            ->where('courses.teacher_id', $teacherId)
            ->avg('grades.grade');
        
        return round($average ?? 0, 2);
    }
    
    /**
     * Obtient le taux de présence d'un étudiant
     * 
     * @param int $studentId
     * @param int $classId
     * @param int $teacherId
     * @return float
     */
    private function getStudentAttendanceRate(int $studentId, int $classId, int $teacherId): float
    {
        $total = DB::table('attendances')
            ->join('courses', 'attendances.course_id', '=', 'courses.id')
            ->where('attendances.user_id', $studentId)
            ->where('courses.class_id', $classId)
            ->where('courses.teacher_id', $teacherId)
            ->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $present = DB::table('attendances')
            ->join('courses', 'attendances.course_id', '=', 'courses.id')
            ->where('attendances.user_id', $studentId)
            ->where('courses.class_id', $classId)
            ->where('courses.teacher_id', $teacherId)
            ->where('attendances.status', 'present')
            ->count();
        
        return round(($present / $total) * 100, 1);
    }
    
    /**
     * Obtient la note moyenne d'un étudiant
     * 
     * @param int $studentId
     * @param int $classId
     * @param int $teacherId
     * @return float
     */
    private function getStudentAverageGrade(int $studentId, int $classId, int $teacherId): float
    {
        $average = DB::table('grades')
            ->join('assignments', 'grades.assignment_id', '=', 'assignments.id')
            ->join('courses', 'assignments.course_id', '=', 'courses.id')
            ->where('grades.user_id', $studentId)
            ->where('courses.class_id', $classId)
            ->where('courses.teacher_id', $teacherId)
            ->avg('grades.grade');
        
        return round($average ?? 0, 2);
    }
    
    /**
     * Obtient les détails de présence d'un étudiant
     * 
     * @param int $studentId
     * @param int $classId
     * @param int $teacherId
     * @return array
     */
    private function getStudentAttendanceDetails(int $studentId, int $classId, int $teacherId): array
    {
        $attendances = DB::table('attendances')
            ->join('courses', 'attendances.course_id', '=', 'courses.id')
            ->where('attendances.user_id', $studentId)
            ->where('courses.class_id', $classId)
            ->where('courses.teacher_id', $teacherId)
            ->select('attendances.status', DB::raw('COUNT(*) as count'))
            ->groupBy('attendances.status')
            ->pluck('count', 'status')
            ->toArray();
        
        $total = array_sum($attendances);
        
        return [
            'present' => $attendances['present'] ?? 0,
            'absent' => $attendances['absent'] ?? 0,
            'late' => $attendances['late'] ?? 0,
            'excused' => $attendances['excused'] ?? 0,
            'total' => $total,
            'rate' => $total > 0 ? round((($attendances['present'] ?? 0) / $total) * 100, 1) : 0,
        ];
    }
    
    /**
     * Obtient les données de progression d'un étudiant
     * 
     * @param int $studentId
     * @param int $classId
     * @param int $teacherId
     * @return array
     */
    private function getStudentProgressionData(int $studentId, int $classId, int $teacherId): array
    {
        $grades = DB::table('grades')
            ->join('assignments', 'grades.assignment_id', '=', 'assignments.id')
            ->join('courses', 'assignments.course_id', '=', 'courses.id')
            ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
            ->where('grades.user_id', $studentId)
            ->where('courses.class_id', $classId)
            ->where('courses.teacher_id', $teacherId)
            ->select([
                'subjects.name as subject',
                'grades.grade',
                'grades.created_at'
            ])
            ->orderBy('grades.created_at', 'asc')
            ->get();
        
        return [
            'labels' => $grades->pluck('created_at')->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray(),
            'grades' => $grades->pluck('grade')->toArray(),
            'subjects' => $grades->pluck('subject')->toArray(),
        ];
    }
    
    /**
     * Compte les devoirs en attente de correction
     * 
     * @param int $classId
     * @param int $teacherId
     * @return int
     */
    private function getPendingGradesCount(int $classId, int $teacherId): int
    {
        return DB::table('assignments')
            ->join('courses', 'assignments.course_id', '=', 'courses.id')
            ->leftJoin('grades', 'assignments.id', '=', 'grades.assignment_id')
            ->where('courses.class_id', $classId)
            ->where('courses.teacher_id', $teacherId)
            ->whereNull('grades.id')
            ->where('assignments.due_date', '<', Carbon::now())
            ->count();
    }
    
    /**
     * Génère un fichier Excel
     * 
     * @param $students
     * @param ClassModel $class
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    private function generateExcel($students, ClassModel $class)
    {
        // À implémenter avec Laravel Excel ou similaire
        // return Excel::download(new StudentsExport($students), "classe-{$class->name}.xlsx");
    }
    
    /**
     * Génère un fichier CSV
     * 
     * @param $students
     * @param ClassModel $class
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    private function generateCSV($students, ClassModel $class)
    {
        // À implémenter
    }
    
    /**
     * Génère un fichier PDF
     * 
     * @param $students
     * @param ClassModel $class
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    private function generatePDF($students, ClassModel $class)
    {
        // À implémenter avec DomPDF ou similaire
    }
}