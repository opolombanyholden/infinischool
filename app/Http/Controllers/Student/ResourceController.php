<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:student']);
    }

    public function index(Request $request)
    {
        $student = auth()->user();

        $query = Resource::public()
            ->whereHas('course.class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with(['course', 'subject', 'teacher']);

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $resources = $query->ordered()->paginate(20);

        // Cours pour le filtre
        $courses = Course::whereHas('class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->with('subject')
            ->get();

        return view('student.resources.index', compact('resources', 'courses'));
    }

    public function download($id)
    {
        $student = auth()->user();

        $resource = Resource::public()
            ->whereHas('course.class.students', function($q) use ($student) {
                $q->where('users.id', $student->id);
            })
            ->findOrFail($id);

        // Incrémenter le compteur
        $resource->incrementDownloads();

        return Storage::disk('public')->download($resource->file_path, $resource->file_name);
    }
}