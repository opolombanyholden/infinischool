<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Recording;
use App\Services\ZoomService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * TeacherCourseController
 * 
 * Gère la programmation des cours, cours en direct et enregistrements
 * Intégration avec Zoom API pour génération de liens de visioconférence
 * 
 * @package App\Http\Controllers\Teacher
 */
class TeacherCourseController extends Controller
{
    protected ZoomService $zoomService;
    
    public function __construct(ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }
    
    /**
     * Liste de tous les cours de l'enseignant
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $teacher = Auth::user();
        $filter = $request->input('filter', 'all'); // all, scheduled, live, completed, cancelled
        
        $query = Course::where('teacher_id', $teacher->id)
            ->with(['class', 'subject', 'recordings']);
        
        // Filtres
        if ($filter !== 'all') {
            $query->where('status', $filter);
        }
        
        // Recherche
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('subject', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Tri
        $sortBy = $request->input('sort_by', 'scheduled_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $courses = $query->paginate(15);
        
        // Statistiques
        $stats = [
            'total' => Course::where('teacher_id', $teacher->id)->count(),
            'scheduled' => Course::where('teacher_id', $teacher->id)->where('status', 'scheduled')->count(),
            'completed' => Course::where('teacher_id', $teacher->id)->where('status', 'completed')->count(),
            'live' => Course::where('teacher_id', $teacher->id)->where('status', 'live')->count(),
        ];
        
        return view('teacher.courses.index', compact('courses', 'stats', 'filter'));
    }
    
    /**
     * Affiche le formulaire de création de cours
     * 
     * @return View
     */
    public function create(): View
    {
        $teacher = Auth::user();
        
        // Classes assignées à l'enseignant
        $classes = ClassModel::whereHas('teachers', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->get();
        
        // Matières enseignées par le professeur
        $subjects = Subject::whereHas('teachers', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->get();
        
        // Créneaux disponibles (basés sur l'emploi du temps)
        $availableSlots = $this->getAvailableTimeSlots($teacher->id);
        
        return view('teacher.courses.create', compact('classes', 'subjects', 'availableSlots'));
    }
    
    /**
     * Enregistre un nouveau cours
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'scheduled_at' => 'required|date|after:now',
            'duration' => 'required|integer|min:15|max:240', // 15min à 4h
            'type' => 'required|in:lecture,tutorial,lab,exam',
            'allow_recording' => 'boolean',
            'auto_attendance' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $teacher = Auth::user();
        
        // Vérifier les conflits d'horaires
        $hasConflict = $this->checkScheduleConflict(
            $teacher->id,
            $request->input('scheduled_at'),
            $request->input('duration')
        );
        
        if ($hasConflict) {
            return redirect()->back()
                ->with('error', 'Un cours est déjà programmé à cet horaire.')
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Créer le cours
            $course = Course::create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'teacher_id' => $teacher->id,
                'class_id' => $request->input('class_id'),
                'subject_id' => $request->input('subject_id'),
                'scheduled_at' => $request->input('scheduled_at'),
                'duration' => $request->input('duration'),
                'type' => $request->input('type'),
                'status' => 'scheduled',
                'allow_recording' => $request->boolean('allow_recording'),
                'auto_attendance' => $request->boolean('auto_attendance'),
            ]);
            
            // Générer le lien Zoom
            $zoomMeeting = $this->zoomService->createMeeting([
                'topic' => $course->title,
                'start_time' => Carbon::parse($course->scheduled_at)->toIso8601String(),
                'duration' => $course->duration,
                'settings' => [
                    'host_video' => true,
                    'participant_video' => true,
                    'join_before_host' => false,
                    'mute_upon_entry' => true,
                    'auto_recording' => $course->allow_recording ? 'cloud' : 'none',
                ],
            ]);
            
            // Mettre à jour le cours avec les infos Zoom
            $course->update([
                'meeting_id' => $zoomMeeting['id'],
                'meeting_url' => $zoomMeeting['join_url'],
                'meeting_password' => $zoomMeeting['password'] ?? null,
            ]);
            
            // Envoyer des notifications aux étudiants
            $this->notifyStudents($course);
            
            DB::commit();
            
            return redirect()->route('teacher.courses.show', $course)
                ->with('success', 'Cours programmé avec succès !');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la programmation du cours: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Affiche les détails d'un cours
     * 
     * @param Course $course
     * @return View
     */
    public function show(Course $course): View
    {
        // Vérifier que l'enseignant a accès à ce cours
        $this->authorize('view', $course);
        
        $course->load([
            'class.students',
            'subject',
            'attendances.user',
            'recordings',
            'resources',
            'assignments.grades',
        ]);
        
        // Statistiques du cours
        $stats = [
            'total_students' => $course->class->students->count(),
            'present_count' => $course->attendances()->where('status', 'present')->count(),
            'absent_count' => $course->attendances()->where('status', 'absent')->count(),
            'resources_count' => $course->resources->count(),
            'recordings_count' => $course->recordings->count(),
        ];
        
        return view('teacher.courses.show', compact('course', 'stats'));
    }
    
    /**
     * Affiche le formulaire d'édition
     * 
     * @param Course $course
     * @return View
     */
    public function edit(Course $course): View
    {
        $this->authorize('update', $course);
        
        // Ne peut modifier que les cours pas encore commencés
        if ($course->status !== 'scheduled') {
            abort(403, 'Ce cours ne peut plus être modifié.');
        }
        
        $teacher = Auth::user();
        
        $classes = ClassModel::whereHas('teachers', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->get();
        
        $subjects = Subject::whereHas('teachers', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->get();
        
        return view('teacher.courses.edit', compact('course', 'classes', 'subjects'));
    }
    
    /**
     * Met à jour un cours
     * 
     * @param Request $request
     * @param Course $course
     * @return RedirectResponse
     */
    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);
        
        if ($course->status !== 'scheduled') {
            return redirect()->back()
                ->with('error', 'Ce cours ne peut plus être modifié.');
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'duration' => 'required|integer|min:15|max:240',
            'type' => 'required|in:lecture,tutorial,lab,exam',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Mettre à jour le cours
            $course->update($request->only([
                'title',
                'description',
                'scheduled_at',
                'duration',
                'type',
            ]));
            
            // Mettre à jour le meeting Zoom si les dates ont changé
            if ($request->input('scheduled_at') !== $course->getOriginal('scheduled_at')) {
                $this->zoomService->updateMeeting($course->meeting_id, [
                    'topic' => $course->title,
                    'start_time' => Carbon::parse($course->scheduled_at)->toIso8601String(),
                    'duration' => $course->duration,
                ]);
                
                // Notifier les étudiants du changement
                $this->notifyStudentsOfChange($course);
            }
            
            DB::commit();
            
            return redirect()->route('teacher.courses.show', $course)
                ->with('success', 'Cours mis à jour avec succès !');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Annule un cours
     * 
     * @param Course $course
     * @return RedirectResponse
     */
    public function cancel(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);
        
        if ($course->status === 'completed' || $course->status === 'cancelled') {
            return redirect()->back()
                ->with('error', 'Ce cours ne peut pas être annulé.');
        }
        
        try {
            DB::beginTransaction();
            
            $course->update(['status' => 'cancelled']);
            
            // Annuler le meeting Zoom
            $this->zoomService->deleteMeeting($course->meeting_id);
            
            // Notifier les étudiants
            $this->notifyStudentsOfCancellation($course);
            
            DB::commit();
            
            return redirect()->route('teacher.courses.index')
                ->with('success', 'Cours annulé avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'annulation: ' . $e->getMessage());
        }
    }
    
    /**
     * Démarre un cours (passe en mode live)
     * 
     * @param Course $course
     * @return RedirectResponse
     */
    public function start(Course $course): RedirectResponse
    {
        $this->authorize('update', $course);
        
        if ($course->status !== 'scheduled') {
            return redirect()->back()
                ->with('error', 'Ce cours ne peut pas être démarré.');
        }
        
        // Vérifier que l'heure est correcte (max 15 min avant)
        $now = Carbon::now();
        $scheduledTime = Carbon::parse($course->scheduled_at);
        
        if ($now->diffInMinutes($scheduledTime, false) > 15) {
            return redirect()->back()
                ->with('error', 'Ce cours ne peut être démarré que 15 minutes avant l\'heure prévue.');
        }
        
        $course->update([
            'status' => 'live',
            'started_at' => Carbon::now(),
        ]);
        
        // Créer les enregistrements de présence automatiques si activé
        if ($course->auto_attendance) {
            $this->createAttendanceRecords($course);
        }
        
        return redirect()->route('teacher.courses.live', $course)
            ->with('success', 'Cours démarré !');
    }
    
    /**
     * Interface de cours en direct
     * 
     * @param Course $course
     * @return View
     */
    public function live(Course $course): View
    {
        $this->authorize('view', $course);
        
        if ($course->status !== 'live') {
            abort(403, 'Ce cours n\'est pas en direct.');
        }
        
        $course->load(['class.students', 'attendances']);
        
        // Étudiants présents en temps réel
        $presentStudents = $course->attendances()
            ->where('status', 'present')
            ->with('user')
            ->get();
        
        return view('teacher.courses.live', compact('course', 'presentStudents'));
    }
    
    /**
     * Termine un cours
     * 
     * @param Course $course
     * @return RedirectResponse
     */
    public function end(Course $course): RedirectResponse
    {
        $this->authorize('update', $course);
        
        if ($course->status !== 'live') {
            return redirect()->back()
                ->with('error', 'Ce cours n\'est pas en direct.');
        }
        
        $course->update([
            'status' => 'completed',
            'ended_at' => Carbon::now(),
        ]);
        
        // Récupérer les enregistrements Zoom si disponibles
        if ($course->allow_recording) {
            $this->fetchZoomRecording($course);
        }
        
        return redirect()->route('teacher.courses.show', $course)
            ->with('success', 'Cours terminé avec succès !');
    }
    
    /**
     * Liste des enregistrements
     * 
     * @return View
     */
    public function recordings(): View
    {
        $teacher = Auth::user();
        
        $recordings = Recording::whereHas('course', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->with(['course.class', 'course.subject'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('teacher.courses.recordings', compact('recordings'));
    }
    
    /**
     * Vérifie les conflits d'horaires
     * 
     * @param int $teacherId
     * @param string $scheduledAt
     * @param int $duration
     * @return bool
     */
    private function checkScheduleConflict(int $teacherId, string $scheduledAt, int $duration): bool
    {
        $start = Carbon::parse($scheduledAt);
        $end = $start->copy()->addMinutes($duration);
        
        return Course::where('teacher_id', $teacherId)
            ->where('status', '!=', 'cancelled')
            ->where(function($query) use ($start, $end) {
                $query->whereBetween('scheduled_at', [$start, $end])
                    ->orWhere(function($q) use ($start, $end) {
                        $q->where('scheduled_at', '<=', $start)
                          ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration MINUTE) >= ?', [$start]);
                    });
            })
            ->exists();
    }
    
    /**
     * Récupère les créneaux disponibles
     * 
     * @param int $teacherId
     * @return array
     */
    private function getAvailableTimeSlots(int $teacherId): array
    {
        // Emploi du temps de base du professeur
        $schedules = Schedule::where('teacher_id', $teacherId)
            ->where('is_available', true)
            ->get();
        
        // Cours déjà programmés
        $bookedSlots = Course::where('teacher_id', $teacherId)
            ->where('status', '!=', 'cancelled')
            ->where('scheduled_at', '>=', Carbon::now())
            ->get(['scheduled_at', 'duration']);
        
        // Fusionner et retourner les créneaux libres
        return $this->calculateFreeSlots($schedules, $bookedSlots);
    }
    
    /**
     * Calcule les créneaux libres
     * 
     * @param $schedules
     * @param $bookedSlots
     * @return array
     */
    private function calculateFreeSlots($schedules, $bookedSlots): array
    {
        // Logique pour calculer les créneaux disponibles
        // À implémenter selon les besoins spécifiques
        return [];
    }
    
    /**
     * Crée les enregistrements de présence automatiques
     * 
     * @param Course $course
     * @return void
     */
    private function createAttendanceRecords(Course $course): void
    {
        $students = $course->class->students;
        
        foreach ($students as $student) {
            DB::table('attendances')->insert([
                'course_id' => $course->id,
                'user_id' => $student->id,
                'status' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
    
    /**
     * Récupère l'enregistrement Zoom
     * 
     * @param Course $course
     * @return void
     */
    private function fetchZoomRecording(Course $course): void
    {
        // À implémenter : récupération asynchrone via queue/job
        // RecordingJob::dispatch($course);
    }
    
    /**
     * Notifie les étudiants d'un nouveau cours
     * 
     * @param Course $course
     * @return void
     */
    private function notifyStudents(Course $course): void
    {
        // À implémenter : envoi de notifications
    }
    
    /**
     * Notifie les étudiants d'un changement
     * 
     * @param Course $course
     * @return void
     */
    private function notifyStudentsOfChange(Course $course): void
    {
        // À implémenter
    }
    
    /**
     * Notifie les étudiants d'une annulation
     * 
     * @param Course $course
     * @return void
     */
    private function notifyStudentsOfCancellation(Course $course): void
    {
        // À implémenter
    }
}