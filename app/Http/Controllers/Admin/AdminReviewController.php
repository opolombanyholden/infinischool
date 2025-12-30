<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Formation;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * AdminReviewController
 * 
 * Gère la modération des avis des formations
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminReviewController extends Controller
{
    /**
     * Affiche la liste des avis
     */
    public function index(Request $request): View
    {
        $query = Review::with(['user', 'formation']);

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par formation
        if ($request->filled('formation_id')) {
            $query->where('formation_id', $request->formation_id);
        }

        // Filtre par note
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $reviews = $query->latest()->paginate(20);

        // Stats
        $stats = [
            'total' => Review::count(),
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
            'average_rating' => round(Review::where('status', 'approved')->avg('rating') ?? 0, 1),
        ];

        // Formations pour le filtre
        $formations = Formation::orderBy('name')->get();

        return view('admin.reviews.index', compact('reviews', 'stats', 'formations'));
    }

    /**
     * Affiche les avis signalés/problématiques
     */
    public function flagged(): View
    {
        // Mots-clés sensibles
        $badWords = ['arnaque', 'nul', 'escroquerie', 'voleur', 'mauvais', 'horrible'];

        $query = Review::with(['user', 'formation']);

        // Rechercher les avis avec mots sensibles ou notes très basses
        $query->where(function ($q) use ($badWords) {
            foreach ($badWords as $word) {
                $q->orWhere('comment', 'like', "%{$word}%");
            }
            $q->orWhere('rating', '<=', 2);
        });

        $reviews = $query->latest()->paginate(20);

        return view('admin.reviews.flagged', compact('reviews'));
    }

    /**
     * Approuve un avis
     */
    public function approve(Review $review): RedirectResponse
    {
        $review->update(['status' => 'approved']);

        return redirect()
            ->back()
            ->with('success', 'Avis approuvé avec succès !');
    }

    /**
     * Rejette un avis
     */
    public function reject(Request $request, Review $review): RedirectResponse
    {
        $review->update(['status' => 'rejected']);

        return redirect()
            ->back()
            ->with('success', 'Avis rejeté avec succès !');
    }

    /**
     * Supprime un avis
     */
    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()
            ->back()
            ->with('success', 'Avis supprimé avec succès !');
    }
}
