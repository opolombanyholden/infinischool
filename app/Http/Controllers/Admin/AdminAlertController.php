<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * AdminAlertController
 * 
 * Gère les alertes système et notifications de masse
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminAlertController extends Controller
{
    /**
     * Affiche la liste des alertes
     */
    public function index(Request $request): View
    {
        $query = Alert::query();

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $alerts = $query->latest()->paginate(15);

        // Stats
        $stats = [
            'total' => Alert::count(),
            'active' => Alert::where('is_active', true)->count(),
            'info' => Alert::where('type', 'info')->count(),
            'warning' => Alert::where('type', 'warning')->count(),
            'danger' => Alert::where('type', 'danger')->count(),
        ];

        return view('admin.alerts.index', compact('alerts', 'stats'));
    }

    /**
     * Envoie une nouvelle alerte
     */
    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => 'required|in:info,warning,danger',
            'recipients' => 'required|array',
            'recipients.*' => 'in:all,students,teachers,admins',
        ]);

        // Déterminer le rôle cible
        $targetRole = null;
        if (in_array('all', $validated['recipients'])) {
            $targetRole = null; // Tous les utilisateurs
        } elseif (in_array('students', $validated['recipients'])) {
            $targetRole = 'student';
        } elseif (in_array('teachers', $validated['recipients'])) {
            $targetRole = 'teacher';
        }

        // Créer l'alerte
        $alert = Alert::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'target_role' => $targetRole,
            'is_active' => true,
        ]);

        // Créer les notifications pour les utilisateurs concernés
        $usersQuery = User::query();
        if ($targetRole) {
            $usersQuery->where('role', $targetRole);
        }

        $users = $usersQuery->get();
        $notificationCount = 0;

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'system_alert',
                'title' => $validated['title'],
                'message' => $validated['message'],
                'data' => json_encode(['alert_id' => $alert->id, 'alert_type' => $validated['type']]),
                'is_read' => false,
            ]);
            $notificationCount++;
        }

        return redirect()
            ->route('admin.alerts.index')
            ->with('success', "Alerte envoyée avec succès à {$notificationCount} utilisateur(s) !");
    }

    /**
     * Active ou désactive une alerte
     */
    public function toggle(Alert $alert): RedirectResponse
    {
        $alert->update(['is_active' => !$alert->is_active]);

        $status = $alert->is_active ? 'activée' : 'désactivée';

        return redirect()
            ->back()
            ->with('success', "Alerte {$status} avec succès !");
    }

    /**
     * Supprime une alerte
     */
    public function destroy(Alert $alert): RedirectResponse
    {
        $alert->delete();

        return redirect()
            ->back()
            ->with('success', 'Alerte supprimée avec succès !');
    }
}
