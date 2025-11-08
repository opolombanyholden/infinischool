<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * AdminFormationController
 * 
 * Gère la gestion complète des formations de la plateforme
 * CRUD, prix, prérequis, matières, statistiques
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminFormationController extends Controller
{
    /**
     * Affiche la liste des formations avec filtres
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Formation::query();
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtre par niveau
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        
        // Filtre par catégorie
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        // Ajouter les compteurs
        $query->withCount(['enrollments', 'classes', 'subjects']);
        
        // Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Pagination
        $perPage = $request->input('per_page', 20);
        $formations = $query->paginate($perPage)->withQueryString();
        
        // Statistiques globales
        $stats = [
            'total' => Formation::count(),
            'active' => Formation::where('status', 'active')->count(),
            'draft' => Formation::where('status', 'draft')->count(),
            'archived' => Formation::where('status', 'archived')->count(),
            'total_enrollments' => Enrollment::count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
        ];
        
        // Catégories disponibles pour le filtre
        $categories = Formation::distinct('category')->pluck('category');
        
        return view('admin.formations.index', compact('formations', 'stats', 'categories'));
    }
    
    /**
     * Affiche le formulaire de création de formation
     * 
     * @return View
     */
    public function create(): View
    {
        // Liste des niveaux disponibles
        $levels = [
            'debutant' => 'Débutant',
            'intermediaire' => 'Intermédiaire',
            'avance' => 'Avancé',
            'expert' => 'Expert',
        ];
        
        // Liste des formations pour les prérequis
        $allFormations = Formation::where('status', 'active')
            ->orderBy('name')
            ->get();
        
        return view('admin.formations.create', compact('levels', 'allFormations'));
    }
    
    /**
     * Enregistre une nouvelle formation
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:formations,code',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'level' => ['required', Rule::in(['debutant', 'intermediaire', 'avance', 'expert'])],
            'category' => 'required|string|max:100',
            'duration_weeks' => 'required|integer|min:1',
            'duration_hours' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'status' => ['required', Rule::in(['active', 'draft', 'archived'])],
            'image' => 'nullable|image|max:5120', // 5MB max
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'exists:formations,id',
            'objectives' => 'nullable|array',
            'objectives.*' => 'string|max:255',
            'max_students_per_class' => 'required|integer|min:1|max:50',
            'is_featured' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);
        
        // Générer un slug unique
        $validated['slug'] = Str::slug($validated['name']);
        $originalSlug = $validated['slug'];
        $counter = 1;
        
        while (Formation::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        // Upload de l'image
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('formations', 'public');
        }
        
        // Convertir les objectifs en JSON
        if (isset($validated['objectives'])) {
            $validated['objectives'] = json_encode($validated['objectives']);
        }
        
        // Extraire les prérequis pour les sauvegarder séparément
        $prerequisites = $validated['prerequisites'] ?? [];
        unset($validated['prerequisites']);
        
        DB::beginTransaction();
        
        try {
            // Créer la formation
            $formation = Formation::create($validated);
            
            // Attacher les prérequis
            if (!empty($prerequisites)) {
                $formation->prerequisites()->attach($prerequisites);
            }
            
            DB::commit();
            
            return redirect()
                ->route('admin.formations.show', $formation)
                ->with('success', 'Formation créée avec succès !');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Supprimer l'image si l'insertion échoue
            if (isset($validated['image'])) {
                Storage::disk('public')->delete($validated['image']);
            }
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }
    
    /**
     * Affiche les détails d'une formation
     * 
     * @param Formation $formation
     * @return View
     */
    public function show(Formation $formation): View
    {
        // Charger les relations
        $formation->load(['prerequisites', 'subjects', 'classes', 'enrollments']);
        
        // Statistiques de la formation
        $stats = $this->getFormationStats($formation);
        
        // Dernières inscriptions
        $recentEnrollments = Enrollment::with('student')
            ->where('formation_id', $formation->id)
            ->latest()
            ->limit(10)
            ->get();
        
        // Classes associées
        $classes = ClassModel::where('formation_id', $formation->id)
            ->withCount('students')
            ->get();
        
        // Matières (subjects)
        $subjects = Subject::where('formation_id', $formation->id)->get();
        
        return view('admin.formations.show', compact(
            'formation',
            'stats',
            'recentEnrollments',
            'classes',
            'subjects'
        ));
    }
    
    /**
     * Affiche le formulaire d'édition de formation
     * 
     * @param Formation $formation
     * @return View
     */
    public function edit(Formation $formation): View
    {
        // Charger les prérequis actuels
        $formation->load('prerequisites');
        
        // Liste des niveaux disponibles
        $levels = [
            'debutant' => 'Débutant',
            'intermediaire' => 'Intermédiaire',
            'avance' => 'Avancé',
            'expert' => 'Expert',
        ];
        
        // Liste des formations pour les prérequis (exclure la formation actuelle)
        $allFormations = Formation::where('status', 'active')
            ->where('id', '!=', $formation->id)
            ->orderBy('name')
            ->get();
        
        // Décoder les objectifs JSON
        $objectives = json_decode($formation->objectives, true) ?? [];
        
        return view('admin.formations.edit', compact('formation', 'levels', 'allFormations', 'objectives'));
    }
    
    /**
     * Met à jour une formation
     * 
     * @param Request $request
     * @param Formation $formation
     * @return RedirectResponse
     */
    public function update(Request $request, Formation $formation): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('formations')->ignore($formation->id)],
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'level' => ['required', Rule::in(['debutant', 'intermediaire', 'avance', 'expert'])],
            'category' => 'required|string|max:100',
            'duration_weeks' => 'required|integer|min:1',
            'duration_hours' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'status' => ['required', Rule::in(['active', 'draft', 'archived'])],
            'image' => 'nullable|image|max:5120',
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'exists:formations,id',
            'objectives' => 'nullable|array',
            'objectives.*' => 'string|max:255',
            'max_students_per_class' => 'required|integer|min:1|max:50',
            'is_featured' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);
        
        // Générer un nouveau slug si le nom a changé
        if ($validated['name'] !== $formation->name) {
            $validated['slug'] = Str::slug($validated['name']);
            $originalSlug = $validated['slug'];
            $counter = 1;
            
            while (Formation::where('slug', $validated['slug'])
                ->where('id', '!=', $formation->id)
                ->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }
        
        // Upload de la nouvelle image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($formation->image) {
                Storage::disk('public')->delete($formation->image);
            }
            $validated['image'] = $request->file('image')
                ->store('formations', 'public');
        }
        
        // Convertir les objectifs en JSON
        if (isset($validated['objectives'])) {
            $validated['objectives'] = json_encode($validated['objectives']);
        }
        
        // Extraire les prérequis
        $prerequisites = $validated['prerequisites'] ?? [];
        unset($validated['prerequisites']);
        
        DB::beginTransaction();
        
        try {
            // Mettre à jour la formation
            $formation->update($validated);
            
            // Synchroniser les prérequis
            $formation->prerequisites()->sync($prerequisites);
            
            DB::commit();
            
            return redirect()
                ->route('admin.formations.show', $formation)
                ->with('success', 'Formation mise à jour avec succès !');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }
    
    /**
     * Supprime une formation
     * 
     * @param Formation $formation
     * @return RedirectResponse
     */
    public function destroy(Formation $formation): RedirectResponse
    {
        // Vérifier qu'il n'y a pas d'inscriptions actives
        $activeEnrollments = Enrollment::where('formation_id', $formation->id)
            ->where('status', 'active')
            ->count();
        
        if ($activeEnrollments > 0) {
            return redirect()
                ->route('admin.formations.show', $formation)
                ->with('error', "Impossible de supprimer : {$activeEnrollments} inscriptions actives.");
        }
        
        // Vérifier qu'il n'y a pas de classes actives
        $activeClasses = ClassModel::where('formation_id', $formation->id)
            ->where('status', 'active')
            ->count();
        
        if ($activeClasses > 0) {
            return redirect()
                ->route('admin.formations.show', $formation)
                ->with('error', "Impossible de supprimer : {$activeClasses} classes actives.");
        }
        
        DB::beginTransaction();
        
        try {
            // Supprimer l'image
            if ($formation->image) {
                Storage::disk('public')->delete($formation->image);
            }
            
            // Détacher les prérequis
            $formation->prerequisites()->detach();
            
            // Supprimer la formation
            $formation->delete();
            
            DB::commit();
            
            return redirect()
                ->route('admin.formations.index')
                ->with('success', 'Formation supprimée avec succès !');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
    
    /**
     * Duplique une formation
     * 
     * @param Formation $formation
     * @return RedirectResponse
     */
    public function duplicate(Formation $formation): RedirectResponse
    {
        DB::beginTransaction();
        
        try {
            // Créer une copie
            $newFormation = $formation->replicate();
            $newFormation->name = $formation->name . ' (Copie)';
            $newFormation->code = $formation->code . '-COPY-' . time();
            $newFormation->slug = Str::slug($newFormation->name);
            $newFormation->status = 'draft';
            $newFormation->save();
            
            // Copier les prérequis
            $prerequisites = $formation->prerequisites->pluck('id')->toArray();
            $newFormation->prerequisites()->attach($prerequisites);
            
            // Copier les matières
            foreach ($formation->subjects as $subject) {
                $newSubject = $subject->replicate();
                $newSubject->formation_id = $newFormation->id;
                $newSubject->save();
            }
            
            DB::commit();
            
            return redirect()
                ->route('admin.formations.edit', $newFormation)
                ->with('success', 'Formation dupliquée avec succès ! Pensez à modifier le code et le nom.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la duplication : ' . $e->getMessage());
        }
    }
    
    /**
     * Archive une formation
     * 
     * @param Formation $formation
     * @return RedirectResponse
     */
    public function archive(Formation $formation): RedirectResponse
    {
        $formation->update(['status' => 'archived']);
        
        return redirect()
            ->back()
            ->with('success', 'Formation archivée avec succès !');
    }
    
    /**
     * Active une formation
     * 
     * @param Formation $formation
     * @return RedirectResponse
     */
    public function activate(Formation $formation): RedirectResponse
    {
        $formation->update(['status' => 'active']);
        
        return redirect()
            ->back()
            ->with('success', 'Formation activée avec succès !');
    }
    
    /**
     * Gère les matières (subjects) d'une formation
     * 
     * @param Formation $formation
     * @return View
     */
    public function subjects(Formation $formation): View
    {
        $subjects = Subject::where('formation_id', $formation->id)
            ->orderBy('order')
            ->get();
        
        return view('admin.formations.subjects', compact('formation', 'subjects'));
    }
    
    /**
     * Ajoute une matière à une formation
     * 
     * @param Request $request
     * @param Formation $formation
     * @return RedirectResponse
     */
    public function addSubject(Request $request, Formation $formation): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'hours' => 'required|integer|min:1',
            'order' => 'nullable|integer|min:0',
            'is_mandatory' => 'boolean',
        ]);
        
        $validated['formation_id'] = $formation->id;
        
        // Si pas d'ordre spécifié, mettre à la fin
        if (!isset($validated['order'])) {
            $maxOrder = Subject::where('formation_id', $formation->id)->max('order') ?? 0;
            $validated['order'] = $maxOrder + 1;
        }
        
        Subject::create($validated);
        
        return redirect()
            ->route('admin.formations.subjects', $formation)
            ->with('success', 'Matière ajoutée avec succès !');
    }
    
    /**
     * Récupère les statistiques d'une formation
     * 
     * @param Formation $formation
     * @return array
     */
    private function getFormationStats(Formation $formation): array
    {
        // Inscriptions
        $totalEnrollments = Enrollment::where('formation_id', $formation->id)->count();
        $activeEnrollments = Enrollment::where('formation_id', $formation->id)
            ->where('status', 'active')
            ->count();
        $completedEnrollments = Enrollment::where('formation_id', $formation->id)
            ->where('status', 'completed')
            ->count();
        
        // Taux de complétion
        $completionRate = $totalEnrollments > 0 
            ? ($completedEnrollments / $totalEnrollments) * 100 
            : 0;
        
        // Revenus
        $totalRevenue = Payment::where('formation_id', $formation->id)
            ->where('status', 'completed')
            ->sum('amount');
        
        $pendingRevenue = Payment::where('formation_id', $formation->id)
            ->where('status', 'pending')
            ->sum('amount');
        
        // Note moyenne
        $avgRating = DB::table('reviews')
            ->where('formation_id', $formation->id)
            ->avg('rating') ?? 0;
        
        // Nombre de classes
        $totalClasses = ClassModel::where('formation_id', $formation->id)->count();
        $activeClasses = ClassModel::where('formation_id', $formation->id)
            ->where('status', 'active')
            ->count();
        
        return [
            'enrollments' => [
                'total' => $totalEnrollments,
                'active' => $activeEnrollments,
                'completed' => $completedEnrollments,
                'completion_rate' => round($completionRate, 1),
            ],
            'revenue' => [
                'total' => round($totalRevenue, 2),
                'pending' => round($pendingRevenue, 2),
            ],
            'classes' => [
                'total' => $totalClasses,
                'active' => $activeClasses,
            ],
            'rating' => round($avgRating, 1),
        ];
    }
}