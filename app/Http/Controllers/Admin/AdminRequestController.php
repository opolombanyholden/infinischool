<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Formation;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * AdminRequestController
 * 
 * Gère les demandes en attente (inscriptions enseignants, validations formations)
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminRequestController extends Controller
{
    /**
     * Affiche la liste des demandes en attente
     */
    public function index(Request $request): View
    {
        // Demandes d'inscription enseignant en attente
        $pendingTeachers = User::where('role', 'teacher')
            ->where('is_approved', false)
            ->latest()
            ->get();

        // Formations en attente de validation
        $pendingFormations = Formation::where('status', 'pending')
            ->with(['teacher'])
            ->latest()
            ->get();

        // Générer une liste unifiée de demandes
        $requests = collect();

        foreach ($pendingTeachers as $teacher) {
            $requests->push([
                'id' => 'teacher_' . $teacher->id,
                'type' => 'teacher_registration',
                'title' => 'Inscription enseignant',
                'description' => $teacher->name . ' souhaite devenir enseignant',
                'user' => $teacher,
                'entity' => $teacher,
                'entity_type' => 'teacher',
                'entity_id' => $teacher->id,
                'date' => $teacher->created_at,
            ]);
        }

        foreach ($pendingFormations as $formation) {
            $requests->push([
                'id' => 'formation_' . $formation->id,
                'type' => 'formation_validation',
                'title' => 'Validation formation',
                'description' => 'Formation "' . $formation->name . '" en attente de validation',
                'user' => $formation->teacher,
                'entity' => $formation,
                'entity_type' => 'formation',
                'entity_id' => $formation->id,
                'date' => $formation->created_at,
            ]);
        }

        // Trier par date
        $requests = $requests->sortByDesc('date')->values();

        // Stats
        $stats = [
            'total' => $requests->count(),
            'teachers' => $pendingTeachers->count(),
            'formations' => $pendingFormations->count(),
        ];

        return view('admin.requests.index', compact('requests', 'stats'));
    }

    /**
     * Affiche les détails d'une demande
     */
    public function show($id): View
    {
        // Parser l'ID (format: type_id)
        [$type, $entityId] = explode('_', $id, 2);

        if ($type === 'teacher') {
            $entity = User::findOrFail($entityId);
            $entityType = 'teacher';
        } else {
            $entity = Formation::with(['teacher'])->findOrFail($entityId);
            $entityType = 'formation';
        }

        return view('admin.requests.show', compact('entity', 'entityType'));
    }

    /**
     * Approuve une demande
     */
    public function approve(Request $request, $id): RedirectResponse
    {
        [$type, $entityId] = explode('_', $id, 2);

        if ($type === 'teacher') {
            $user = User::findOrFail($entityId);
            $user->update(['is_approved' => true]);
            $message = 'Inscription enseignant approuvée !';
        } else {
            $formation = Formation::findOrFail($entityId);
            $formation->update(['status' => 'published']);
            $message = 'Formation validée et publiée !';
        }

        return redirect()
            ->route('admin.requests.index')
            ->with('success', $message);
    }

    /**
     * Rejette une demande
     */
    public function reject(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        [$type, $entityId] = explode('_', $id, 2);

        if ($type === 'teacher') {
            $user = User::findOrFail($entityId);
            // Optionnel: stocker la raison du rejet ou envoyer un email
            $user->update(['is_approved' => false, 'role' => 'student']); // Rétrograder en étudiant
            $message = 'Demande d\'inscription enseignant rejetée.';
        } else {
            $formation = Formation::findOrFail($entityId);
            $formation->update(['status' => 'rejected']);
            $message = 'Formation rejetée.';
        }

        return redirect()
            ->route('admin.requests.index')
            ->with('success', $message);
    }
}
