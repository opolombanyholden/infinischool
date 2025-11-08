<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:student']);
    }

    public function index(Request $request)
    {
        $student = auth()->user();

        $query = Assignment::published()
            ->whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'course', 'teacher']);

        // Filtres
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'overdue':
                    $query->overdue();
                    break;
                case 'completed':
                    $query->whereHas('submissions', function($q) use ($student) {
                        $q->where('student_id', $student->id)
                          ->whereIn('status', ['submitted', 'graded']);
                    });
                    break;
            }
        }

        $assignments = $query->orderBy('due_date', 'asc')->paginate(15);

        return view('student.assignments.index', compact('assignments'));
    }

    public function show($id)
    {
        $student = auth()->user();

        $assignment = Assignment::published()
            ->whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'course', 'teacher'])
            ->findOrFail($id);

        // Récupérer ou créer la soumission
        $submission = AssignmentSubmission::firstOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ],
            [
                'status' => 'draft',
            ]
        );

        return view('student.assignments.show', compact('assignment', 'submission'));
    }

    public function submit(Request $request, $id)
    {
        $student = auth()->user();

        $assignment = Assignment::published()->findOrFail($id);

        $validated = $request->validate([
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:10240', // 10MB
        ]);

        // Upload de la pièce jointe
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')
                ->store('assignments/submissions', 'public');
        }

        // Mettre à jour ou créer la soumission
        $submission = AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ],
            [
                'content' => $validated['content'],
                'attachment_path' => $attachmentPath ?? AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->where('student_id', $student->id)
                    ->value('attachment_path'),
                'submitted_at' => now(),
                'status' => 'submitted',
                'is_late' => $assignment->due_date->isPast(),
            ]
        );

        return redirect()->route('student.assignments.show', $assignment->id)
            ->with('success', 'Votre devoir a été soumis avec succès !');
    }
}