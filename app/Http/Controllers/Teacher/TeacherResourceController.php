<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseResource;
use App\Models\Course;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * TeacherResourceController
 * 
 * Gère les ressources pédagogiques et fichiers de l'enseignant
 * Upload, organisation, partage de documents et médias
 * 
 * @package App\Http\Controllers\Teacher
 */
class TeacherResourceController extends Controller
{
    // Types de fichiers autorisés
    private const ALLOWED_TYPES = [
        'document' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'],
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'video' => ['mp4', 'avi', 'mov', 'wmv', 'webm'],
        'audio' => ['mp3', 'wav', 'ogg', 'm4a'],
        'archive' => ['zip', 'rar', '7z', 'tar', 'gz'],
    ];
    
    // Taille maximale par type (en Mo)
    private const MAX_SIZE = [
        'document' => 10,
        'image' => 5,
        'video' => 100,
        'audio' => 20,
        'archive' => 50,
    ];
    
    /**
     * Affiche la bibliothèque de ressources
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $teacher = Auth::user();
        
        $query = CourseResource::where('teacher_id', $teacher->id);
        
        // Filtres
        if ($request->has('type') && $request->input('type') !== 'all') {
            $query->where('type', $request->input('type'));
        }
        
        if ($request->has('course_id') && $request->input('course_id') !== 'all') {
            $query->where('course_id', $request->input('course_id'));
        }
        
        // Recherche
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('filename', 'like', "%{$search}%");
            });
        }
        
        // Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $resources = $query->with(['course.class', 'course.subject'])->paginate(20);
        
        // Statistiques
        $stats = [
            'total' => CourseResource::where('teacher_id', $teacher->id)->count(),
            'documents' => CourseResource::where('teacher_id', $teacher->id)->where('type', 'document')->count(),
            'images' => CourseResource::where('teacher_id', $teacher->id)->where('type', 'image')->count(),
            'videos' => CourseResource::where('teacher_id', $teacher->id)->where('type', 'video')->count(),
            'total_size' => $this->getTotalStorageUsed($teacher->id),
        ];
        
        // Cours disponibles pour filtres
        $courses = Course::where('teacher_id', $teacher->id)
            ->with('subject')
            ->orderBy('scheduled_at', 'desc')
            ->limit(50)
            ->get();
        
        return view('teacher.resources.index', compact('resources', 'stats', 'courses'));
    }
    
    /**
     * Affiche le formulaire d'upload
     * 
     * @return View
     */
    public function create(): View
    {
        $teacher = Auth::user();
        
        // Cours disponibles
        $courses = Course::where('teacher_id', $teacher->id)
            ->where('scheduled_at', '>=', Carbon::now()->subDays(7))
            ->with(['class', 'subject'])
            ->orderBy('scheduled_at', 'desc')
            ->get();
        
        // Classes pour partage direct
        $classes = ClassModel::whereHas('teachers', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->get();
        
        return view('teacher.resources.create', compact('courses', 'classes'));
    }
    
    /**
     * Upload un fichier (AJAX)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:102400', // 100 Mo max
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'nullable|exists:courses,id',
            'type' => 'nullable|in:document,image,video,audio,archive,other',
            'is_public' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $teacher = Auth::user();
            $file = $request->file('file');
            
            // Déterminer le type de fichier
            $extension = $file->getClientOriginalExtension();
            $type = $this->determineFileType($extension);
            
            // Vérifier la taille selon le type
            $maxSize = self::MAX_SIZE[$type] ?? 10;
            if ($file->getSize() > $maxSize * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => "La taille maximale pour ce type de fichier est de {$maxSize} Mo.",
                ], 422);
            }
            
            // Générer un nom unique
            $filename = $this->generateUniqueFilename($file);
            
            // Stocker le fichier
            $path = $file->storeAs(
                "teachers/{$teacher->id}/resources",
                $filename,
                'private'
            );
            
            // Créer l'enregistrement
            $resource = CourseResource::create([
                'teacher_id' => $teacher->id,
                'course_id' => $request->input('course_id'),
                'title' => $request->input('title') ?: $file->getClientOriginalName(),
                'description' => $request->input('description'),
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'path' => $path,
                'type' => $type,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'extension' => $extension,
                'is_public' => $request->boolean('is_public'),
                'downloads' => 0,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Fichier uploadé avec succès !',
                'resource' => $resource->load(['course.class', 'course.subject']),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload : ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Upload multiple (drag & drop)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'file|max:102400',
            'course_id' => 'nullable|exists:courses,id',
            'is_public' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $teacher = Auth::user();
        $uploaded = [];
        $errors = [];
        
        foreach ($request->file('files') as $file) {
            try {
                $extension = $file->getClientOriginalExtension();
                $type = $this->determineFileType($extension);
                $filename = $this->generateUniqueFilename($file);
                
                $path = $file->storeAs(
                    "teachers/{$teacher->id}/resources",
                    $filename,
                    'private'
                );
                
                $resource = CourseResource::create([
                    'teacher_id' => $teacher->id,
                    'course_id' => $request->input('course_id'),
                    'title' => $file->getClientOriginalName(),
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $type,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'extension' => $extension,
                    'is_public' => $request->boolean('is_public'),
                    'downloads' => 0,
                ]);
                
                $uploaded[] = $resource;
                
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return response()->json([
            'success' => count($uploaded) > 0,
            'message' => count($uploaded) . ' fichier(s) uploadé(s) sur ' . count($request->file('files')),
            'uploaded' => $uploaded,
            'errors' => $errors,
        ]);
    }
    
    /**
     * Affiche les détails d'une ressource
     * 
     * @param CourseResource $resource
     * @return View
     */
    public function show(CourseResource $resource): View
    {
        $this->authorize('view', $resource);
        
        $resource->load(['course.class', 'course.subject']);
        
        // Statistiques d'utilisation
        $stats = [
            'downloads' => $resource->downloads,
            'views' => $resource->views ?? 0,
            'last_downloaded' => $resource->last_downloaded_at,
        ];
        
        return view('teacher.resources.show', compact('resource', 'stats'));
    }
    
    /**
     * Affiche le formulaire d'édition
     * 
     * @param CourseResource $resource
     * @return View
     */
    public function edit(CourseResource $resource): View
    {
        $this->authorize('update', $resource);
        
        $teacher = Auth::user();
        
        $courses = Course::where('teacher_id', $teacher->id)
            ->with(['class', 'subject'])
            ->orderBy('scheduled_at', 'desc')
            ->get();
        
        return view('teacher.resources.edit', compact('resource', 'courses'));
    }
    
    /**
     * Met à jour une ressource
     * 
     * @param Request $request
     * @param CourseResource $resource
     * @return RedirectResponse
     */
    public function update(Request $request, CourseResource $resource): RedirectResponse
    {
        $this->authorize('update', $resource);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'nullable|exists:courses,id',
            'is_public' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $resource->update($request->only([
            'title',
            'description',
            'course_id',
            'is_public',
        ]));
        
        return redirect()->route('teacher.resources.show', $resource)
            ->with('success', 'Ressource mise à jour avec succès !');
    }
    
    /**
     * Télécharge un fichier
     * 
     * @param CourseResource $resource
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(CourseResource $resource)
    {
        $this->authorize('view', $resource);
        
        // Incrémenter le compteur de téléchargements
        $resource->increment('downloads');
        $resource->update(['last_downloaded_at' => Carbon::now()]);
        
        return Storage::disk('private')->download(
            $resource->path,
            $resource->original_filename
        );
    }
    
    /**
     * Supprime une ressource
     * 
     * @param CourseResource $resource
     * @return RedirectResponse
     */
    public function destroy(CourseResource $resource): RedirectResponse
    {
        $this->authorize('delete', $resource);
        
        try {
            // Supprimer le fichier physique
            Storage::disk('private')->delete($resource->path);
            
            // Supprimer l'enregistrement
            $resource->delete();
            
            return redirect()->route('teacher.resources.index')
                ->with('success', 'Ressource supprimée avec succès !');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
    
    /**
     * Suppression multiple
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function destroyMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'resource_ids' => 'required|array',
            'resource_ids.*' => 'exists:course_resources,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $teacher = Auth::user();
        $deleted = 0;
        $errors = [];
        
        foreach ($request->input('resource_ids') as $resourceId) {
            try {
                $resource = CourseResource::where('id', $resourceId)
                    ->where('teacher_id', $teacher->id)
                    ->firstOrFail();
                
                Storage::disk('private')->delete($resource->path);
                $resource->delete();
                $deleted++;
                
            } catch (\Exception $e) {
                $errors[] = [
                    'resource_id' => $resourceId,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return response()->json([
            'success' => $deleted > 0,
            'message' => "{$deleted} ressource(s) supprimée(s)",
            'errors' => $errors,
        ]);
    }
    
    /**
     * Partage une ressource avec une classe
     * 
     * @param Request $request
     * @param CourseResource $resource
     * @return JsonResponse
     */
    public function share(Request $request, CourseResource $resource): JsonResponse
    {
        $this->authorize('update', $resource);
        
        $validator = Validator::make($request->all(), [
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:classes,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            // Synchroniser les classes autorisées
            $resource->sharedWithClasses()->sync($request->input('class_ids'));
            
            return response()->json([
                'success' => true,
                'message' => 'Ressource partagée avec succès !',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtient un aperçu du fichier (pour images/PDFs)
     * 
     * @param CourseResource $resource
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function preview(CourseResource $resource)
    {
        $this->authorize('view', $resource);
        
        // Seuls certains types peuvent être prévisualisés
        if (!in_array($resource->type, ['image', 'document'])) {
            abort(400, 'Ce type de fichier ne peut pas être prévisualisé.');
        }
        
        return Storage::disk('private')->response($resource->path);
    }
    
    /**
     * Détermine le type de fichier basé sur l'extension
     * 
     * @param string $extension
     * @return string
     */
    private function determineFileType(string $extension): string
    {
        $extension = strtolower($extension);
        
        foreach (self::ALLOWED_TYPES as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }
        
        return 'other';
    }
    
    /**
     * Génère un nom de fichier unique
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    private function generateUniqueFilename($file): string
    {
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $basename = Str::slug($basename);
        
        return $basename . '_' . time() . '_' . Str::random(8) . '.' . $extension;
    }
    
    /**
     * Calcule l'espace de stockage utilisé
     * 
     * @param int $teacherId
     * @return string
     */
    private function getTotalStorageUsed(int $teacherId): string
    {
        $totalBytes = CourseResource::where('teacher_id', $teacherId)->sum('size');
        
        return $this->formatBytes($totalBytes);
    }
    
    /**
     * Formate les octets en unité lisible
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}