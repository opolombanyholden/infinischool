<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Attendance;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:student']);
    }

    public function index()
    {
        $student = auth()->user();

        $courses = Course::whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'teacher', 'class'])
            ->latest('scheduled_at')
            ->paginate(15);

        return view('student.courses.index', compact('courses'));
    }

    public function show($id)
    {
        $student = auth()->user();

        $course = Course::whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'teacher', 'class', 'resources', 'assignments'])
            ->findOrFail($id);

        // Vérifier la présence
        $attendance = Attendance::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->first();

        // Enregistrement du cours
        $recording = $course->getLatestRecording();

        return view('student.courses.show', compact('course', 'attendance', 'recording'));
    }

    public function join($id)
    {
        $student = auth()->user();

        $course = Course::whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->findOrFail($id);

        if (!$course->canJoin()) {
            return redirect()->route('student.courses.show', $course->id)
                ->with('error', 'Ce cours n\'est pas encore accessible.');
        }

        // Marquer la présence automatiquement
        $attendance = Attendance::firstOrCreate(
            [
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => $course->scheduled_at->toDateString(),
            ],
            [
                'class_id' => $course->class_id,
                'enrollment_id' => $student->enrollments()->where('class_id', $course->class_id)->first()->id ?? null,
                'status' => 'present',
                'check_in_time' => now(),
            ]
        );

        $attendance->markPresent();

        return redirect($course->meeting_url);
    }

    public function schedule()
    {
        $student = auth()->user();

        $courses = Course::scheduled()
            ->whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'teacher', 'class'])
            ->whereBetween('scheduled_at', [
                now()->startOfWeek(),
                now()->endOfWeek()->addWeeks(4)
            ])
            ->orderBy('scheduled_at')
            ->get();

        return view('student.courses.schedule', compact('courses'));
    }
}