<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Notification;
use App\Models\Grade;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:student']);
    }

    public function index()
    {
        $student = auth()->user();

        // Inscriptions actives
        $enrollments = Enrollment::active()
            ->where('student_id', $student->id)
            ->with(['formation', 'class'])
            ->get();

        // Cours de la semaine
        $weekCourses = Course::thisWeek()
            ->whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'teacher', 'class'])
            ->upcoming()
            ->get();

        // Prochain cours
        $nextCourse = $weekCourses->first();

        // Cours live maintenant
        $liveCourses = Course::live()
            ->whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'teacher'])
            ->get();

        // Devoirs à rendre (prochains 7 jours)
        $upcomingAssignments = Assignment::published()
            ->upcoming()
            ->whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->where('due_date', '<=', now()->addDays(7))
            ->with(['subject', 'course'])
            ->get();

        // Dernières notes
        $recentGrades = Grade::byStudent($student->id)
            ->recent(30)
            ->with(['subject', 'course'])
            ->latest()
            ->limit(5)
            ->get();

        // Notifications non lues
        $notifications = Notification::unread()
            ->where('user_id', $student->id)
            ->recent(7)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Statistiques
        $stats = [
            'total_enrollments' => $enrollments->count(),
            'average_grade' => Grade::byStudent($student->id)->avg('grade') ?? 0,
            'average_grade_on_20' => $recentGrades->avg(fn($g) => $g->getGradeOn20()) ?? 0,
            'attendance_rate' => $this->calculateAttendanceRate($student->id),
            'completed_courses' => Course::completed()
                ->whereHas('class.students', function($q) use ($student) {
                    $q->where('users.id', $student->id);
                })
                ->count(),
            'pending_assignments' => $upcomingAssignments->count(),
        ];

        // Progression moyenne de toutes les inscriptions
        $averageProgress = $enrollments->avg('progress_percentage') ?? 0;

        return view('student.dashboard', compact(
            'enrollments',
            'weekCourses',
            'nextCourse',
            'liveCourses',
            'upcomingAssignments',
            'recentGrades',
            'notifications',
            'stats',
            'averageProgress'
        ));
    }

    private function calculateAttendanceRate($studentId)
    {
        $totalAttendances = \App\Models\Attendance::byStudent($studentId)->count();
        if ($totalAttendances === 0) return 100;

        $presentCount = \App\Models\Attendance::byStudent($studentId)->present()->count();
        return round(($presentCount / $totalAttendances) * 100, 2);
    }
}