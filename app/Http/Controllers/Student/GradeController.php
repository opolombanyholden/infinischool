<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:student']);
    }

    public function index(Request $request)
    {
        $student = auth()->user();

        $query = Grade::byStudent($student->id)
            ->with(['subject', 'course', 'teacher']);

        // Filtre par matière
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $grades = $query->latest('graded_at')->paginate(20);

        // Statistiques
        $stats = [
            'average_grade' => Grade::byStudent($student->id)->avg('grade') ?? 0,
            'average_on_20' => $grades->avg(fn($g) => $g->getGradeOn20()) ?? 0,
            'total_grades' => Grade::byStudent($student->id)->count(),
            'passed_count' => Grade::byStudent($student->id)->passed()->count(),
            'failed_count' => Grade::byStudent($student->id)->failed()->count(),
        ];

        // Matières pour le filtre
        $subjects = Subject::whereHas('grades', function($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->get();

        // Moyennes par matière
        $subjectAverages = Grade::byStudent($student->id)
            ->selectRaw('subject_id, AVG(grade) as avg_grade, COUNT(*) as total')
            ->groupBy('subject_id')
            ->with('subject')
            ->get();

        return view('student.grades.index', compact('grades', 'stats', 'subjects', 'subjectAverages'));
    }

    public function show($id)
    {
        $grade = Grade::byStudent(auth()->id())
            ->with(['subject', 'course', 'teacher'])
            ->findOrFail($id);

        return view('student.grades.show', compact('grade'));
    }
}