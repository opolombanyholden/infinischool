<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Subject;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    /**
     * Afficher la liste des formations
     */
    public function index(Request $request)
    {
        $query = Formation::active()->published();

        // Filtre par niveau
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Filtre par catégorie
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filtre par prix
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortBy = $request->get('sort', 'popular');
        switch ($sortBy) {
            case 'recent':
                $query->latest();
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
            default:
                // ✅ CORRECTION: Changé 'students_count' en 'enrolled_count'
                $query->orderBy('enrolled_count', 'desc');
                break;
        }

        $formations = $query->paginate(12);

        // Données pour les filtres
        $levels = Formation::distinct()->pluck('level');
        $categories = Formation::distinct()->pluck('category');

        return view('formations.index', compact('formations', 'levels', 'categories'));
    }

    /**
     * Afficher le détail d'une formation
     */
    public function show($slug)
    {
        $formation = Formation::active()
            ->published()
            ->where('slug', $slug)
            ->with(['subjects', 'classes'])
            ->firstOrFail();

        // Incrémenter les vues (si colonne existe)
        if (\Schema::hasColumn('formations', 'views_count')) {
            $formation->increment('views_count');
        }

        // Formations similaires
        $similarFormations = Formation::active()
            ->published()
            ->where('level', $formation->level)
            ->where('id', '!=', $formation->id)
            // ✅ CORRECTION: Changé 'students_count' en 'enrolled_count'
            ->orderBy('enrolled_count', 'desc')
            ->limit(3)
            ->get();

        // Avis (TODO: créer modèle Review)
        $reviews = [];

        return view('formations.show', compact('formation', 'similarFormations', 'reviews'));
    }

    /**
     * Inscription à une formation
     */
    public function enroll(Request $request, Formation $formation)
    {
        // Vérifier que l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour vous inscrire.');
        }

        // Vérifier que l'utilisateur est un étudiant
        if (auth()->user()->role !== 'student') {
            return back()->with('error', 'Seuls les étudiants peuvent s\'inscrire à une formation.');
        }

        // Vérifier si déjà inscrit
        $existingEnrollment = Enrollment::where('student_id', auth()->id())
            ->where('formation_id', $formation->id)
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($existingEnrollment) {
            return back()->with('error', 'Vous êtes déjà inscrit à cette formation.');
        }

        // Valider les données
        $validated = $request->validate([
            'class_id' => 'nullable|exists:classes,id',
            'payment_method' => 'required|in:card,paypal,bank_transfer',
        ]);

        // Créer l'inscription
        $enrollment = Enrollment::create([
            'student_id' => auth()->id(),
            'formation_id' => $formation->id,
            'class_id' => $validated['class_id'] ?? null,
            'enrollment_date' => now(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Créer le paiement (si modèle Payment existe)
        if (class_exists('\App\Models\Payment')) {
            $payment = $enrollment->payments()->create([
                'student_id' => auth()->id(),
                'formation_id' => $formation->id,
                'amount' => $formation->discounted_price,
                'currency' => 'eur',
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
            ]);

            // Rediriger vers le paiement
            return redirect()->route('payments.process', $payment->id)
                ->with('success', 'Votre inscription a été enregistrée. Veuillez procéder au paiement.');
        }

        return redirect()->route('student.dashboard')
            ->with('success', 'Votre inscription a été enregistrée avec succès !');
    }

    /**
     * Catalogue des formations par catégorie
     */
    public function category($category)
    {
        $formations = Formation::active()
            ->published()
            ->where('category', $category)
            // ✅ CORRECTION: Changé 'students_count' en 'enrolled_count'
            ->orderBy('enrolled_count', 'desc')
            ->paginate(12);

        return view('formations.category', compact('formations', 'category'));
    }

    /**
     * Diplômes et certifications disponibles
     */
    public function certificates()
    {
        $formations = Formation::active()
            ->published()
            ->where('certificate_available', true)
            // ✅ CORRECTION: Changé 'students_count' en 'enrolled_count'
            ->orderBy('enrolled_count', 'desc')
            ->get();

        return view('formations.certificates', compact('formations'));
    }

    /**
     * Recherche de formations
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        $formations = Formation::active()
            ->published()
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('objectives', 'like', "%{$query}%");
            })
            ->orderBy('enrolled_count', 'desc')
            ->paginate(12);

        return view('formations.search', compact('formations', 'query'));
    }
}