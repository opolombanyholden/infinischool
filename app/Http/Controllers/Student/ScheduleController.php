<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Course;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:student']);
    }

    /**
     * Afficher l'emploi du temps de l'étudiant
     */
    public function index(Request $request)
    {
        $student = auth()->user();

        // Récupérer les emplois du temps de l'étudiant
        $schedules = Schedule::whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'teacher', 'class', 'course'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Organiser par jour de la semaine
        $scheduleByDay = $schedules->groupBy('day_of_week');

        // Jours de la semaine
        $daysOfWeek = [
            'monday' => 'Lundi',
            'tuesday' => 'Mardi',
            'wednesday' => 'Mercredi',
            'thursday' => 'Jeudi',
            'friday' => 'Vendredi',
            'saturday' => 'Samedi',
            'sunday' => 'Dimanche',
        ];

        return view('student.schedule.index', compact('scheduleByDay', 'daysOfWeek'));
    }

    /**
     * Afficher le calendrier des cours
     */
    public function calendar(Request $request)
    {
        $student = auth()->user();

        // Date de début (défaut: début du mois)
        $startDate = $request->filled('start') 
            ? Carbon::parse($request->start)
            : now()->startOfMonth();

        // Date de fin (défaut: fin du mois)
        $endDate = $request->filled('end')
            ? Carbon::parse($request->end)
            : now()->endOfMonth();

        // Récupérer tous les cours de la période
        $courses = Course::whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'teacher', 'class'])
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->get();

        // Récupérer les devoirs de la période
        $assignments = Assignment::published()
            ->whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'course'])
            ->whereBetween('due_date', [$startDate, $endDate])
            ->get();

        // Préparer les événements pour le calendrier
        $events = $this->prepareCalendarEvents($courses, $assignments);

        return view('student.schedule.calendar', compact('events', 'startDate', 'endDate'));
    }

    /**
     * Vue hebdomadaire de l'emploi du temps
     */
    public function week(Request $request)
    {
        $student = auth()->user();

        // Date de la semaine (défaut: cette semaine)
        $weekStart = $request->filled('date')
            ? Carbon::parse($request->date)->startOfWeek()
            : now()->startOfWeek();

        $weekEnd = $weekStart->copy()->endOfWeek();

        // Récupérer les cours de la semaine
        $courses = Course::whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'teacher', 'class'])
            ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
            ->orderBy('scheduled_at')
            ->get();

        // Organiser les cours par jour
        $coursesByDay = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $coursesByDay[$date->format('Y-m-d')] = [
                'date' => $date,
                'day_name' => $date->isoFormat('dddd'),
                'courses' => $courses->filter(function($course) use ($date) {
                    return $course->scheduled_at->isSameDay($date);
                })->values()
            ];
        }

        // Navigation semaine précédente/suivante
        $previousWeek = $weekStart->copy()->subWeek();
        $nextWeek = $weekStart->copy()->addWeek();

        return view('student.schedule.week', compact('coursesByDay', 'weekStart', 'previousWeek', 'nextWeek'));
    }

    /**
     * Vue journalière de l'emploi du temps
     */
    public function day(Request $request)
    {
        $student = auth()->user();

        // Date du jour (défaut: aujourd'hui)
        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : now();

        // Récupérer les cours du jour
        $courses = Course::whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'teacher', 'class', 'attendances' => function($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->whereDate('scheduled_at', $date)
            ->orderBy('scheduled_at')
            ->get();

        // Récupérer les devoirs à rendre aujourd'hui
        $assignmentsDue = Assignment::published()
            ->whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'course'])
            ->whereDate('due_date', $date)
            ->get();

        // Navigation jour précédent/suivant
        $previousDay = $date->copy()->subDay();
        $nextDay = $date->copy()->addDay();

        return view('student.schedule.day', compact('courses', 'assignmentsDue', 'date', 'previousDay', 'nextDay'));
    }

    /**
     * Exporter l'emploi du temps (iCal format)
     */
    public function export(Request $request)
    {
        $student = auth()->user();

        // Période d'export (défaut: 3 mois)
        $startDate = now();
        $endDate = now()->addMonths(3);

        // Récupérer tous les cours de la période
        $courses = Course::whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['subject', 'teacher', 'class'])
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->get();

        // Générer le fichier iCal
        $ical = $this->generateICalendar($courses, $student);

        // Retourner le fichier
        return response($ical)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="emploi-du-temps.ics"');
    }

    /**
     * Imprimer l'emploi du temps
     */
    public function print(Request $request)
    {
        $student = auth()->user();

        // Type d'emploi du temps (week ou month)
        $type = $request->get('type', 'week');

        if ($type === 'week') {
            $weekStart = now()->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $courses = Course::whereHas('class.students', function($q) use ($student) {
                    $q->where('users.id', $student->id);
                })
                ->with(['subject', 'teacher', 'class'])
                ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
                ->orderBy('scheduled_at')
                ->get();

            return view('student.schedule.print-week', compact('courses', 'weekStart'));
        } else {
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();

            $courses = Course::whereHas('class.students', function($q) use ($student) {
                    $q->where('users.id', $student->id);
                })
                ->with(['subject', 'teacher', 'class'])
                ->whereBetween('scheduled_at', [$monthStart, $monthEnd])
                ->orderBy('scheduled_at')
                ->get();

            return view('student.schedule.print-month', compact('courses', 'monthStart'));
        }
    }

    /**
     * Préparer les événements pour le calendrier
     */
    private function prepareCalendarEvents($courses, $assignments)
    {
        $events = [];

        // Ajouter les cours
        foreach ($courses as $course) {
            $events[] = [
                'id' => 'course-' . $course->id,
                'title' => $course->subject->name,
                'start' => $course->scheduled_at->toIso8601String(),
                'end' => $course->getEndTime()->toIso8601String(),
                'type' => 'course',
                'status' => $course->status,
                'color' => $this->getCourseColor($course->status),
                'description' => $course->title,
                'teacher' => $course->teacher->full_name,
                'room' => $course->room,
                'url' => route('student.courses.show', $course->id),
            ];
        }

        // Ajouter les devoirs
        foreach ($assignments as $assignment) {
            $events[] = [
                'id' => 'assignment-' . $assignment->id,
                'title' => '📝 ' . $assignment->title,
                'start' => $assignment->due_date->toIso8601String(),
                'type' => 'assignment',
                'color' => '#dc3545',
                'description' => 'Devoir à rendre',
                'subject' => $assignment->subject->name,
                'url' => route('student.assignments.show', $assignment->id),
            ];
        }

        return $events;
    }

    /**
     * Obtenir la couleur selon le statut du cours
     */
    private function getCourseColor($status)
    {
        return match($status) {
            'scheduled' => '#007bff',
            'live' => '#28a745',
            'completed' => '#6c757d',
            'cancelled' => '#dc3545',
            default => '#007bff'
        };
    }

    /**
     * Générer un fichier iCalendar
     */
    private function generateICalendar($courses, $student)
    {
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//InfiniSchool//Schedule//FR\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= "X-WR-CALNAME:Emploi du temps - {$student->full_name}\r\n";
        $ical .= "X-WR-TIMEZONE:Europe/Paris\r\n";

        foreach ($courses as $course) {
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:course-{$course->id}@infinischool.com\r\n";
            $ical .= "DTSTAMP:" . now()->format('Ymd\THis\Z') . "\r\n";
            $ical .= "DTSTART:" . $course->scheduled_at->format('Ymd\THis\Z') . "\r\n";
            $ical .= "DTEND:" . $course->getEndTime()->format('Ymd\THis\Z') . "\r\n";
            $ical .= "SUMMARY:{$course->subject->name}\r\n";
            $ical .= "DESCRIPTION:{$course->title} - {$course->teacher->full_name}\r\n";
            $ical .= "LOCATION:{$course->room}\r\n";
            $ical .= "STATUS:" . strtoupper($course->status) . "\r\n";
            $ical .= "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }
}