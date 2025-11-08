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
                $query->orderBy('students_count', 'desc');
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
    public function show($id)
    {
        $formation = Formation::active()
            ->published()
            ->with(['subjects', 'classes'])
            ->findOrFail($id);

        // Incrémenter les vues
        $formation->increment('views_count');

        // Formations similaires
        $similarFormations = Formation::active()
            ->published()
            ->where('id', '!=', $formation->id)
            ->where('category', $formation->category)
            ->limit(3)
            ->get();

        // Statistiques
        $stats = [
            'total_students' => $formation->enrollments()->count(),
            'success_rate' => $formation->getSuccessRate(),
            'average_rating' => $formation->getAverageRating(),
            'total_hours' => $formation->getTotalHours(),
        ];

        // Vérifier si l'utilisateur est déjà inscrit
        $isEnrolled = false;
        if (auth()->check()) {
            $isEnrolled = Enrollment::where('student_id', auth()->id())
                ->where('formation_id', $formation->id)
                ->exists();
        }

        return view('formations.show', compact('formation', 'similarFormations', 'stats', 'isEnrolled'));
    }

    /**
     * Afficher le formulaire d'inscription à une formation
     */
    public function enroll($id)
    {
        // Rediriger vers login si non authentifié
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('info', 'Veuillez vous connecter pour vous inscrire.');
        }

        $formation = Formation::active()
            ->published()
            ->findOrFail($id);

        // Vérifier si déjà inscrit
        $existingEnrollment = Enrollment::where('student_id', auth()->id())
            ->where('formation_id', $formation->id)
            ->first();

        if ($existingEnrollment) {
            return redirect()->route('formations.show', $formation->id)
                ->with('warning', 'Vous êtes déjà inscrit à cette formation.');
        }

        // Vérifier les prérequis
        if (!$formation->checkPrerequisites(auth()->user())) {
            return redirect()->route('formations.show', $formation->id)
                ->with('error', 'Vous ne remplissez pas les prérequis pour cette formation.');
        }

        return view('formations.enroll', compact('formation'));
    }

    /**
     * Traiter l'inscription à une formation
     */
    public function processEnrollment(Request $request, $id)
    {
        $validated = $request->validate([
            'motivation' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:card,paypal,bank_transfer',
            'accept_terms' => 'required|accepted',
        ]);

        $formation = Formation::active()
            ->published()
            ->findOrFail($id);

        // Créer l'inscription avec statut pending
        $enrollment = Enrollment::create([
            'student_id' => auth()->id(),
            'formation_id' => $formation->id,
            'enrollment_date' => now(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => $validated['motivation'] ?? null,
        ]);

        // Créer le paiement
        $payment = $enrollment->payments()->create([
            'student_id' => auth()->id(),
            'formation_id' => $formation->id,
            'amount' => $formation->price,
            'currency' => 'eur',
            'payment_method' => $validated['payment_method'],
            'status' => 'pending',
        ]);

        // Rediriger vers le paiement
        return redirect()->route('payments.process', $payment->id)
            ->with('success', 'Votre inscription a été enregistrée. Veuillez procéder au paiement.');
    }

    /**
     * Catalogue des formations par catégorie
     */
    public function category($category)
    {
        $formations = Formation::active()
            ->published()
            ->where('category', $category)
            ->orderBy('students_count', 'desc')
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
            ->where('has_certificate', true)
            ->orderBy('students_count', 'desc')
            ->get();

        return view('formations.certificates', compact('formations'));
    }
}